<?php

namespace App\Services;

use App\DTO\AuthDTO;
use App\Exception\PersistenceException;
use App\Exception\UserNotFoundException;
use App\Repositories\Interfaces\UserRepositoryInterface;
use App\Services\Interfaces\DepartementsServiceInterface;
use Dotenv\Dotenv;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\UnauthorizedException;
use phpCAS;
use Throwable;

readonly class AuthService implements Interfaces\UserServiceInterface {

    public function __construct(
        private UserRepositoryInterface $userRepository,
        private DepartementsServiceInterface $departementsService
    ) {}

    public function handleUserInDatabase(array $data): void {
        $user = $this->userRepository->getUserByEmail($data['email']);

        if(!$this->emailExistsIn12Plus($data['email'])) {
            throw new UnauthorizedException();
        }

        if (!$user) {
            $data["verified_member_role"] = true; // Rôle par défaut à la création
            try {
                $this->userRepository->createUser($data);
                Log::info("Nouvel utilisateur créé automatiquement via login", ['email' => $data['email']]);
            } catch (PersistenceException $e) {
                Log::error("Échec de la création automatique de l'utilisateur", [
                    'email' => $data['email'],
                    'error' => $e->getMessage()
                ]);
                throw $e;
            }
        }
    }

    public function getUserByEmail(string $email): ?Authenticatable {
        return $this->userRepository->getUserByEmail($email);
    }

    public function getCurrentUserId(): int {
        return Auth::id() ?? 0;
    }

    public function readAll(): array {
        $users = $this->userRepository->readAll();

        $authDtos = [];
        foreach ($users as $user) {
            $authDtos[] = new AuthDTO(
                email: $user["email"],
                nom: $user["nom"],
                prenom: $user["prenom"],
                departements: $this->departementsService->departementsIDs($user["departements"]),
                role: $user["role"],
                id: $user["id"]
            );
        }
        try {
            return $authDtos;
        } catch (Throwable $e) {
            Log::error("Erreur lors de la conversion des users en AuthDTO",["message" => $e->getMessage(), "line" => $e->getLine(), "file" => $e->getFile()]);
            throw $e;
        }
    }

    public function delete(int $id): void {
        try {
            $this->userRepository->delete($id);
            Log::info("Utilisateur supprimé de la base", ['id' => $id]);
        } catch (PersistenceException|UserNotFoundException $e) {
            Log::error("Impossible de supprimer l'utilisateur", [
                'id' => $id,
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }

    public function readById(int $id): AuthDTO {
        $user = $this->userRepository->getUserById($id);

        return new AuthDTO(
            email: $user->email,
            nom: $user->nom,
            prenom: $user->prenom,
            departements: $this->departementsService->departementsIDs($user->departements),
            role: $user->role,
            id: $user->id
        );
    }

    public function update(int $id, array $data): void {
        try {
            $this->userRepository->updateUser($id, $data);
            Log::info("Profil utilisateur mis à jour", ['id' => $id]);
        } catch(PersistenceException|UserNotFoundException $e) {
            Log::error("Erreur lors de la mise à jour de l'utilisateur", [
                'id' => $id,
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }

    public function getRole(): string {
        $userId = $this->getCurrentUserId();
        if ($userId === 0) return 'guest';

        $user = $this->userRepository->getUserById($userId);
        return $user->role ?? 'guest';
    }

    public function isAdmin(): bool {
        return $this->getRole() === "admin";
    }

    public function logout(string $returnUrl): void {
        if (str_contains($returnUrl, '/logout')) {
            $returnUrl = url('/');
        }

        Log::info("Déconnexion CAS initiée", ['user_id' => $this->getCurrentUserId()]);

        phpCAS::logout([
            "url" => phpCAS::getServerLoginURL(),
            "service" => $returnUrl
        ]);
    }

    private function emailExistsIn12Plus(string $email): bool
    {
        $url = config('services.12plus.url');

        $tab_post = [
            'codelangue' => 'fr',
            'liste' => 'annuaire',
            'pas_de_session' => 'oui'
        ];

        $session = curl_init();
        curl_setopt_array($session, [
            CURLOPT_URL => $url,
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => $tab_post,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_SSL_VERIFYHOST => 0,
            CURLOPT_SSL_VERIFYPEER => 0,
            CURLOPT_TIMEOUT => 10
        ]);

        $resultat = curl_exec($session);

        if ($resultat === false) {
            curl_close($session);
            return false;
        }

        curl_close($session);

        $tab_listeindividu = json_decode($resultat, true);

        if (!is_array($tab_listeindividu)) {
            return false;
        }

        // 🔎 Extraction rapide de tous les emails
        $emails = array_map('strtolower', array_column($tab_listeindividu, 'email'));
        return in_array(strtolower($email), $emails, true);
    }
}

<?php

namespace App\Services;

use App\DTO\AuthDTO;
use App\Exception\{PersistenceException, UserNotFoundException};
use App\Repositories\Interfaces\UserRepositoryInterface;
use App\Services\Interfaces\{MapDTOServiceInterface, UserServiceInterface};
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\{Auth, Log};
use Illuminate\Validation\UnauthorizedException;
use phpCAS;
use Throwable;

readonly class AuthService implements UserServiceInterface
{
    public function __construct(
        private UserRepositoryInterface $userRepository,
        private MapDTOServiceInterface $mapDTOService,
    ) {}

    // --- AUTHENTIFICATION & SESSION ---

    public function logout(string $returnUrl): void
    {
        if (str_contains($returnUrl, '/logout')) {
            $returnUrl = url('/');
        }

        Log::info("Déconnexion CAS initiée", ['user_id' => $this->getCurrentUserId()]);

        phpCAS::logout([
            "url" => phpCAS::getServerLoginURL(),
            "service" => $returnUrl
        ]);
    }

    public function getCurrentUserId(): int
    {
        return Auth::id() ?? 0;
    }

    public function getRole(): string
    {
        $userId = $this->getCurrentUserId();
        if ($userId === 0) return 'guest';

        try {
            $user = $this->userRepository->getUserById($userId);
            return $user->role ?? 'guest';
        } catch (UserNotFoundException) {
            return 'guest';
        }
    }

    public function isAdmin(): bool
    {
        return $this->getRole() === "admin";
    }

    // --- LECTURE & RECHERCHE ---

    public function getUsers(?string $searchQuery): Collection
    {
        $users = ($searchQuery && trim($searchQuery) !== '')
            ? $this->userRepository->performSearch($searchQuery)
            : $this->userRepository->readAll();

        try {
            return $this->mapDTOService->mapToAuthDTOsCollection($users);
        } catch (Throwable $e) {
            Log::error("Erreur mapping AuthDTO Collection", [
                "message" => $e->getMessage(),
                "file" => $e->getFile()
            ]);
            throw $e;
        }
    }

    public function readById(int $id): AuthDTO
    {
        $user = $this->userRepository->getUserById($id);
        return $this->mapDTOService->mapToAuthDTO($user);
    }

    public function getUserByEmail(string $email): ?Authenticatable
    {
        return $this->userRepository->getUserByEmail($email);
    }

    // --- ECRITURES (CRUD) ---

    public function handleUserInDatabase(array $data): void
    {
        if (!$this->emailExistIn12Plus($data['email'])) {
            Log::warning("Tentative connexion email non-autorisé 12Plus", ['email' => $data['email']]);
            throw new UnauthorizedException();
        }

        $user = $this->userRepository->getUserByEmail($data['email']);

        if (!$user) {
            $data["verified_member_role"] = true;
            try {
                $this->userRepository->createUser($data);
                Log::info("Création automatique utilisateur via login CAS", ['email' => $data['email']]);
            } catch (PersistenceException $e) {
                Log::error("Échec création auto utilisateur", ['error' => $e->getMessage()]);
                throw $e;
            }
        }
    }

    public function update(int $id, array $data): void
    {
        try {
            $this->userRepository->update($id, $data);
            Log::info("Profil utilisateur mis à jour", ['id' => $id]);
        } catch (PersistenceException|UserNotFoundException $e) {
            Log::error("Erreur mise à jour utilisateur", ['id' => $id, 'error' => $e->getMessage()]);
            throw $e;
        }
    }

    public function delete(int $id): void
    {
        try {
            $this->userRepository->delete($id);
            Log::info("Utilisateur supprimé", ['id' => $id]);
        } catch (PersistenceException|UserNotFoundException $e) {
            Log::error("Erreur suppression utilisateur", ['id' => $id, 'error' => $e->getMessage()]);
            throw $e;
        }
    }

    // --- VÉRIFICATIONS EXTERNES ---

    public function emailExistIn12Plus(string $email): bool
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
        curl_close($session);

        if ($resultat === false) return false;

        $tab_listeindividu = json_decode($resultat, true);
        if (!is_array($tab_listeindividu)) return false;

        $emails = array_map('strtolower', array_column($tab_listeindividu, 'email'));
        return in_array(strtolower($email), $emails, true);
    }
}

<?php

namespace App\Services;

use App\DTO\AuthDTO;
use App\Exception\{PersistenceException, UserNotFoundException};
use App\Repositories\Interfaces\UserRepositoryInterface;
use App\Services\Interfaces\{MapDTOServiceInterface, UserServiceInterface};
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\{Auth, Log};
//use Illuminate\Support\Facades\Http;
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

    /**
     * @inheritdoc
     */
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

    /**
     * @inheritdoc
     */
    public function getCurrentUserId(): int
    {
        return Auth::id() ?? 0;
    }

    /**
     * @inheritdoc
     */
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

    // --- LECTURE & RECHERCHE ---

    /**
     * @inheritdoc
     */
    public function getUsers(?string $searchQuery = null): Collection
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

    /**
     * @inheritdoc
     */
    public function getUsersWhereNotIn(array $usersIds) : Collection
    {
        return $this->mapDTOService->mapToAuthDTOsCollection(
            users : $this->userRepository->getExcludeUsers($usersIds)
        );
    }

    /**
     * @inheritdoc
     */
    public function readById(int $id): AuthDTO
    {;
        return $this->mapDTOService->mapToAuthDTO(
            user : $this->userRepository->getUserById($id)
        );
    }

    /**
     * @inheritdoc
     */
    public function getUserByEmail(string $email): ?Authenticatable
    {
        return $this->userRepository->getUserByEmail($email);
    }

    // --- ECRITURES (CRUD) ---

    /**
     * @inheritdoc
     */
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

    /**
     * @inheritdoc
     */
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

    /**
     * @inheritdoc
     */
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

    /**
     * @inheritdoc
     */
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

//    public function emailExistIn12Plus(string $email): bool
//    {
//        try {
//            $response = Http::asForm()
//                ->timeout(10)
//                ->withoutVerifying() // Garde l'équivalent de tes CURLOPT_SSL_VERIFY... à 0 si besoin
//                ->post(config('services.12plus.url') . '/api/annuaire/check-email', [
//                    'email' => $email
//                ]);
//
//            // Retourne true si le statut HTTP est 2xx et que le body vaut "true"
//            return $response->successful() && filter_var($response->body(), FILTER_VALIDATE_BOOLEAN);
//
//        } catch (\Throwable $e) {
//            return false;
//        }
//    }

    /**
     * @inheritdoc
     */
    public function addFavorites(int $ressource_id, string $ressource_type): void
    {
        try {
            $this->userRepository->addFavorites($ressource_id, $ressource_type);
            Log::info("Ressource ajoutée aux favoris", [
                'user_id' => $this->getCurrentUserId(),
                'ressource_id' => $ressource_id,
                'ressource_type' => $ressource_type
            ]);
        } catch (Throwable $e) {
            Log::error("Erreur lors de l'ajout aux favoris dans AuthService", [
                'message' => $e->getMessage()
            ]);
            throw $e;
        }
    }

    /**
     * @inheritdoc
     */
    public function removeFavorites(string $ressource_type, int $ressource_id): void
    {

        try {
            $this->userRepository->removeFavorites($ressource_type,$ressource_id);
            Log::info("Favori supprimé avec succès", [
                'user_id' => $this->getCurrentUserId(),
                'ressource_id' => $ressource_id,
                'ressource_type' => $ressource_type,
            ]);
        } catch (Throwable $e) {
            Log::error("Erreur lors de la suppression du favori dans AuthService", [
                'ressource_id' => $ressource_id,
                'ressource_type' => $ressource_type,
                'message' => $e->getMessage()
            ]);
            throw $e;
        }
    }

    /**
     * @inheritdoc
     */
    public function getFavorites(): Collection
    {
        try {
            // 1. Récupération des favoris bruts (modèles UserFavori avec la relation 'ressource' préchargée)
            $favoris = $this->userRepository->getFavorites();

            // 2. Mapping dynamique vers le bon DTO selon l'instance de la ressource polymorphique
            return $favoris->map(function ($favori) {
                $ressource = $favori->ressource;

                if (!$ressource) {
                    return null;
                }

                // Détection du type de modèle et appel de la méthode de DTO correspondante
                return match (true) {
                    $ressource instanceof \App\Models\Folder   => $this->mapDTOService->mapToFolderDTO($ressource),
                    $ressource instanceof \App\Models\File     => $this->mapDTOService->mapToFileDTO($ressource),
                    $ressource instanceof \App\Models\Document => $this->mapDTOService->mapToDocumentDTO($ressource),
                    default => null,
                };
            })->filter()->values(); // Nettoie les éventuels nulls et réindexe proprement la collection

        } catch (Throwable $e) {
            Log::error("Erreur lors de la récupération ou du mapping des favoris", [
                'user_id' => $this->getCurrentUserId(),
                'message' => $e->getMessage()
            ]);
            throw $e;
        }
    }
}

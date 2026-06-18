<?php

namespace App\Repositories;

use App\Models\UserFavori;
use App\Exception\{PersistenceException, UserNotFoundException, FavoriNotFoundException};
use App\Models\User;
use App\Repositories\Interfaces\UserRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Log;
use Symfony\Component\Finder\Exception\AccessDeniedException;
use Symfony\Component\HttpFoundation\Exception\BadRequestException;
use Symfony\Component\Routing\Exception\ResourceNotFoundException;
use Throwable;

class UserRepository implements UserRepositoryInterface
{
    // --- LECTURE & AUTHENTIFICATION ---

    /**
     * @inheritDoc
     */
    public function getUserByEmail(string $email): ?User
    {
        return User::where('email', $email)->first();
    }

    /**
     * @inheritDoc
     */
    public function getUserById(int $id): User
    {
        $user = User::where("id", $id)->with("groupes:id")->firstOrFail();
        if (!$user) {
            throw new UserNotFoundException("Utilisateur ID $id introuvable.");
        }
        return $user;
    }

    /**
     * @inheritDoc
     */
    public function readAll(): Collection
    {
        return User::with('groupes:id')->get();
    }

    /**
     * @inheritDoc
     */
    public function getExcludeUsers(array $usersIds) : Collection
    {
        return User::whereNotIn('id', $usersIds)
            ->with("groupes:id")
            ->get();
    }

    /**
     * @inheritDoc
     */
    public function performSearch(string $query): Collection
    {
        return User::search($query)
            ->with('groupes:id')
            ->get();
    }

    // --- ÉCRITURE (CRUD) ---

    /**
     * @inheritDoc
     */
    public function createUser(array $data): User
    {
        try {
            $user = new User();
            $user->email = $data['email'];
            $user->nom = $data['nom'];
            $user->prenom = $data['prenom'];
            $user->role = $data['role'] ?? 'user';
            $user->save();

            if (!empty($data['groupes'])) {
                $user->groupes()->attach($data['groupes']);
            }

            return $user;
        } catch (Throwable $t) {
            Log::error("Erreur SQL lors de la création de l'utilisateur", [
                'email' => $data['email'] ?? 'N/A',
                'message' => $t->getMessage()
            ]);
            throw new PersistenceException("Erreur lors de la création de l'utilisateur.", 0, $t);
        }
    }

    /**
     * @inheritDoc
     */
    public function update(int $id, array $data): void
    {
        $user = User::find($id);

        if (!$user) {
            throw new UserNotFoundException("Mise à jour impossible : utilisateur ID $id introuvable.");
        }

        try {
            if (isset($data['nom']))    $user->nom    = $data['nom'];
            if (isset($data['prenom'])) $user->prenom = $data['prenom'];
            if (isset($data['email']))  $user->email  = $data['email'];
            if (isset($data['role']))   $user->role   = $data['role'];

            $user->save();

            if (isset($data['groupes'])) {
                $user->groupes()->sync($data['groupes']);
            }
        } catch (Throwable $t) {
            Log::error("Erreur SQL lors de la mise à jour de l'utilisateur $id", [
                'message' => $t->getMessage(),
                'data' => $data
            ]);
            throw new PersistenceException("Erreur lors de la modification de l'utilisateur.");
        }
    }

    /**
     * @inheritDoc
     */
    public function delete(int $id): void
    {
        $user = User::find($id);

        if (!$user) {
            throw new UserNotFoundException("Suppression impossible : utilisateur ID $id introuvable.");
        }

        try {
            $user->delete();
        } catch (Throwable $t) {
            Log::error("Échec de la suppression SQL de l'utilisateur $id", [
                'message' => $t->getMessage()
            ]);
            throw new PersistenceException("Erreur technique lors de la suppression.");
        }
    }

    public function addFavorites(int $ressource_id, string $ressource_type): void
    {
        // 1. Vérification propre de l'utilisateur
        $userId = auth()->id();
        if (!$userId) {
            throw new UserNotFoundException("Utilisateur introuvable.");
        }

        // 2. Déduction du modèle
        $modelClass = match ($ressource_type) {
            'document' => \App\Models\Document::class,
            'folder'   => \App\Models\Folder::class,
            'file'     => \App\Models\File::class,
            default    => throw new BadRequestException("Type de ressource invalide.", 400),
        };

        // 3. Vérification de l'existence de la ressource
        if (!$modelClass::where('id', $ressource_id)->exists()) {
            throw new ResourceNotFoundException("Cette ressource n'existe plus.", 404);
        }

        // 4. firstOrCreate pour éviter le crash SQL si le favori existe déjà
        UserFavori::firstOrCreate([
            'user_id'        => $userId,
            'ressource_id'   => $ressource_id,
            'ressource_type' => $modelClass,
        ]);
    }

    public function removeFavorites(string $ressource_type, int $ressource_id): void
    {
        $modelClass = match ($ressource_type) {
            'document' => \App\Models\Document::class,
            'folder'   => \App\Models\Folder::class,
            'file'     => \App\Models\File::class,
            default    => throw new BadRequestException("Type de ressource invalide.", 400),
        };

        $favorite = UserFavori::where("ressource_id", $ressource_id)
            ->where("ressource_type", $modelClass)
            ->where("user_id", auth()->id())
            ->first();
        if (!$favorite) {
            throw new FavoriNotFoundException("Ce favori n'existe pas ou a déjà été supprimé.", 404);
        }
        $favorite->delete();
    }

    public function getFavorites(): Collection
    {
        return UserFavori::where('user_id', auth()->id())
            ->with('ressource.groupes:id') // Magie : charge les Documents, Folders ou Files associés d'un coup !
            ->latest() // Optionnel : trie par les ajouts les plus récents en premier
            ->get();
    }
}

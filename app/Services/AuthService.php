<?php

namespace App\Services;

use App\Models\User;
use App\repositories\interfaces\UserRepositoryInterface;
use Illuminate\Support\Facades\Http;

class AuthService implements Interface\UserServiceInterface {
    public function __construct(
        private readonly UserRepositoryInterface $userRepository,
        private readonly string                  $lang = "fr"
    ) {}

    public function handleUserInDatabase(array $data): void {
        $user = $this->userRepository->getUserByEmail($data['email']);

        if (!$user) {
            $url = 	(new Constants())->url;
            $tab_post = array('appel' => "form_rech", 'codelangue' => $this->lang, 'page' => 'listeindividupublic', 'pas_de_session' => 'oui');

            $response = Http::withoutVerifying()->post($url, $tab_post);

            $resultat = $response->body();
            $tab_annuaire=json_decode($resultat,true);

            $tab_annuaire = (new Constants())->decode_utf8_recursive($tab_annuaire);

            $isCranMember = 0;
            foreach ($tab_annuaire as $value) {
                if (isset($value['email']) && $value['email'] === $data["email"]) {
                    $isCranMember = 1;
                    break;
                }
            }
            $data["isCranMember"] = $isCranMember;
            $this->userRepository->createUser($data);
        }

    }

    public function getUserIdByEmail(string $email) : int {
        $user = $this->userRepository->getUserByEmail($email);
        return $user->id;
    }

    public function getUserRoleByEmail(string $email) : string {
        $user = $this->userRepository->getUserByEmail($email);
        return $user->role;
    }

    public function getUserCranMemberByEmail(string $email) {
        $user = $this->userRepository->getUserByEmail($email);
        return $user->verified_member_role ?? 0;
    }

    public function getUserNewsletterCreatorByEmail(string $email) {
        $user = $this->userRepository->getUserByEmail($email);
        return $user->newsletter_role ?? 0;
    }
}

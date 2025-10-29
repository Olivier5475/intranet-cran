<?php

namespace App\Services;

use App\DTO\AuthDTO;
use App\repositories\interfaces\UserRepositoryInterface;
use Illuminate\Support\Facades\Http;

class AuthService implements Interface\UserServiceInterface {
    public function __construct(
        private UserRepositoryInterface $userRepository,
        private string $lang = "fr"
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

    public function getUserByEmail(string $string) : AuthDTO {
        $user = $this->userRepository->getUserByEmail($string);
        return new AuthDTO(
            email : $user->email,
            nom : $user->nom,
            prenom: $user->prenom
        );
    }
}

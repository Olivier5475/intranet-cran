<?php

namespace App\Services;

use App\DTO\AuthDTO;
use App\repositories\interfaces\UserRepositoryInterface;
use App\Services\Interface\DecodageServiceInterface;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Http;

class AuthService implements Interface\UserServiceInterface {
    public function __construct(
        private UserRepositoryInterface $userRepository,
        private DecodageServiceInterface $decodageService,
        private string $lang = "fr"
    ) {}

    /**
     * @throws ConnectionException
     */
    public function handleUserInDatabase(array $data): void {
        $user = $this->userRepository->getUserByEmail($data['email']);

        if (!$user) {
//            $tab_post = array('appel' => "form_rech", 'codelangue' => $this->lang, 'page' => 'listeindividupublic', 'pas_de_session' => 'oui');
//
//            $response = Http::post(config("cas.url"), $tab_post);
//
//            $resultat = $response->body();
//            $tab_annuaire=json_decode($resultat,true);
//
//            $tab_annuaire = $this->decodageService->decode_utf8_recursive($tab_annuaire);
//
//            $isCranMember = false;
//            foreach ($tab_annuaire as $value) {
//                if (isset($value['email']) && $value['email'] === $data["email"]) {
//                    $isCranMember = true;
//                    break;
//                }
//            }
//            $data["verified_member_role"] = $isCranMember;
            $data["verified_member_role"] = true;
            $this->userRepository->createUser($data);
        }

    }

    public function getUserByEmail(string $string) : ?Authenticatable {
        return $this->userRepository->getUserByEmail($string);
    }
}

<?php

namespace App\Services\Interfaces;

interface DepartementsServiceInterface {
    public function getDepartements():array;
    public function departementsIDs($departements):array;
}

<?php

namespace App\Contracts\Services;

interface HitohaVerificationInterface
{
    public function getItemInformation(...$id);
    public function save($items);
}

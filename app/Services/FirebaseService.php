<?php

namespace App\Services;

use App\Exceptions\InvalidFirebaseTokenException;
use Kreait\Firebase\Factory;
use Exception;

class FirebaseService
{
    private $auth;

    public function __construct()
    {
        $factory = (new Factory)
            ->withServiceAccount(config('services.firebase.credentials'))
            ->withProjectId(config('services.firebase.project_id'));

        $this->auth = $factory->createAuth();
    }

    public function verifyIdToken(string $idToken)
    {
        try {
            $verifiedIdToken = $this->auth->verifyIdToken($idToken);
            return $verifiedIdToken->claims()->all();
        } catch (Exception $e) {
            throw new InvalidFirebaseTokenException(__('exceptions.invalid_firebase_token'));
        }
    }
}

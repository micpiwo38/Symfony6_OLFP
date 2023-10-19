<?php

namespace App\Service;

use Symfony\Bundle\MakerBundle\Str;

class JwtService{
    //Generer un Json Web Token
    //Entete + payload + signature + validité 10800 = 3 heures
    /**
     * Generer un jwt
     *
     * @param array $header
     * @param array $payload
     * @param string $secret
     * @param integer $validity
     * @return string
     */
    public function generate(array $header, array $payload, string $secret, int $validity = 10800): string{

        //condition de validité
        if($validity > 0){
            //Temps instant T
            $now = new \DateTimeImmutable();
            $expiration = $now->getTimestamp() + $validity;

        //iat = issue at :
            $payload['iat'] = $now->getTimestamp(); 
        //Expiration
            $payload['exp'] = $expiration;
        }
   

        //Encoder en base 64
        //Transformer au format json + base 64
        //En informatique, base64 est un codage de l'information utilisant 64 caractères, choisis pour être disponibles sur la majorité des systèmes.
        // Défini en tant qu'encodage MIME dans la RFC 2045, 
        //il est principalement utilisé pour la transmission de messages (courrier électronique et forums Usenet) sur Internet.
        $base64header = base64_encode(json_encode($header));
        $base64payload = base64_encode(json_encode($payload));
        //Netoyer les valeures encodées = retait des +,/=
        $base64header = str_replace(['+', '/', '='], ['-', '_', ''], $base64header);
        $base64payload = str_replace(['+', '/', '='], ['-', '_', ''], $base64payload);

        //Generer la signature
        //Generer un secret depuis votre fichier .env
        $secret = base64_encode($secret);
        //La signature
        $signature = hash_hmac('sha256', $base64header.'.'.$base64payload, $secret, true);

        //Encoder le signature
        $base64Signature = base64_encode($signature);

        //Netoyer
        $base64Signature = str_replace(['+', '/', '='], ['-', '_', ''], $base64Signature);

        $jwt = $base64header . '.'. $base64payload . '.' . $base64Signature;

        return $jwt;
    }

    //Verifié la validité du token (correctement formé)

    public function isValid(string $token):bool{
        //header + payload + signature REGEX
        return preg_match(
            '/^[a-zA-Z0-9\-\_\=]+\.[a-zA-Z0-9\-\_\=]+\.[a-zA-Z0-9\-\_\=]+$/',
            $token
        ) === 1;
    }

    //Verifié si le token a expiré
    //Recuperer le payload
    public function getPayload(string $token): array{
        //On demonte le token => des que l'on rencontre un . header . payload . signature
        $array = explode('.', $token);
        //on decode le payload => sa position est bien array[1] => header . payload . signature
        $payload = json_decode(base64_decode($array[1]), true);
        return $payload;
    }

    //Recuperer entete
    public function getHeader(string $token): array{
        //On demonte le token => des que l'on rencontre un . header . payload . signature
        $array = explode('.', $token);
        //on decode le payload => sa position est bien array[1] => header . payload . signature
        $header = json_decode(base64_decode($array[0]), true);
        return $header;
    }

    //Verifié si le token a expriré
    public function isExpired(string $token) : bool {
        $payload = $this->getPayload($token);
        $nom = new \DateTimeImmutable();

        return $payload['exp'] < $nom->getTimestamp();
        
    }

    //Verifié la signature du token
    public function checkSignature(string $token, string $secret){
        //Recup du header + payload
        $header = $this->getHeader($token);
        $payload = $this->getPayload($token);

        //Check de la signtaure
        //On regenere un token => le 0 evite de regenerer la date d'expiration
        $verif_token = $this->generate($header, $payload, $secret,0);
        //Si le token sont egaux => il n'a pas été cormpus
        return $token === $verif_token;

    }
}
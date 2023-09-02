<?php
/*
In aceasta clasa ne ocupam de codificarea si decodificarea tokenurilor 
JSON Web Token (JWT)
*/

class JWTCodec
{
    // pasam cheia secreta 
    public function __construct(private string $key)
    {
        
    }

    // codificare payload din token
    public function encode(array $payload): string
    {
        // se construieste un antet / header JWT care contine informatii
        //despre tipul tokeului si algoritmul de semnare
        $header = json_encode([
            "typ" => "JWT",
            "alg" => "HS256"
        ]);
        // se codifica in base64 URL safe
        $header = $this->base64urlEncode($header);
        
        // payload-ul este serializat in format json si de asemenea se ccodifica base64
        $payload = json_encode($payload);
        $payload = $this->base64urlEncode($payload);
        
        // se genereaza o cheie hashuita utilizand algoritmul SHA256 
        // semnatura se codifica base64
        $signature = hash_hmac("sha256",
                               $header . "." . $payload,
                               $this->key,
                               true);
        $signature = $this->base64urlEncode($signature);
        
        // se returneaza tokenul final in formatul JWT ( header.payload.signature )
        return $header . "." . $payload . "." . $signature;
    }
    
    // decodificare payload din token
    public function decode(string $token): array
    {
        // se verifica formatul tokenului
        if (preg_match("/^(?<header>.+)\.(?<payload>.+)\.(?<signature>.+)$/",
                   $token,
                   $matches) !== 1) {
                       
            throw new InvalidArgumentException("invalid token format");
        }
        
        // se recalculeaza tokenul folosind aceeasi cheie secreta
        $signature = hash_hmac("sha256",
                               $matches["header"] . "." . $matches["payload"],
                               $this->key,
                               true);   
        
        // se codifica base 64
        $signature_from_token = $this->base64urlDecode($matches["signature"]);
        
        // se verifica daca semnaturile coincid
        if ( ! hash_equals($signature, $signature_from_token)) {
            
            throw new InvalidSignatureException;
        }
        
        // se decodifica payload-ul
        $payload = json_decode($this->base64urlDecode($matches["payload"]), true);
        
        // se verifica valabilitatea tokenului
        if($payload["exp"] < time()) {
            throw new TokenExpiredException;
        }

        // se returneaza payloadul sub forma de array asociat
        return $payload;
    }
    
    // codifica un text in base64Url 
    private function base64urlEncode(string $text): string
    {
        return str_replace(
            ["+", "/", "="],
            ["-", "_", ""],
            base64_encode($text)
        );
    }
    
    // decodifica un text din base64Url
    private function base64urlDecode(string $text): string
    {
        return base64_decode(str_replace(
            ["-", "_"],
            ["+", "/"],
            $text)
        );
    }
}




<?php

class PersoanaController
{
    public function __construct(private PersoanaGateway $gateway)
    {
        
    }

    public function processRequest(string $method, ?string $id): void//? accepta si null
    {
        if ($id === null) {
            
            if ($method == "GET") {
                
                echo json_encode($this->gateway->getAll());

            } elseif ($method == "POST") {
                
                echo "create";
            } else {
                $this->respondMethodNotAllowed("GET, POST");
            }
        } else {
            
            $persoana = $this->gateway->get($id);

            if($persoana === false)
            {
                $this->respondNotFound($id);
                return;
            }

            switch ($method) {
                
                case "GET":
                    echo json_encode($persoana);
                    break;
                
                case "PATCH":
                    echo "update $id";
                    break;
                    
                case "DELETE":
                    echo "delete $id";
                    break;
                
                default:
                    $this->respondMethodNotAllowed("GET, PATCH, DELETE");
                    break;
            }
        }
    }

    private function respondMethodNotAllowed(string $allowed_methods): void
    {
        http_response_code(405);
        header("Allow: $allowed_methods");
    }
    private function respondNotFound(string $id): void
    {
        http_response_code(404);
        echo json_encode(["message" => "Persoana cu ID $id nu a fost gasita."]);
    }
}
<?php

class PersoanaController
{
    public function __construct(private PersoanaGateway $gateway)
    {
        
    }

    public function processRequest(string $method, ?string $id): void//? accepta si null
    {
        if ($id === null) { //daca nu se transmite niciun index
// ################### /api/persoana
            if ($method == "GET") { // prin request GET
                // vom returna toate persoanele din tabel
                echo json_encode($this->gateway->getAll());

            } elseif ($method == "POST") { // prin request POST
                
                //transmitem prin body json din insomnia spre php cu phpinput si json_decode
                $data = (array) json_decode(file_get_contents("php://input"),true);
                
                //verificam datele
                $errors = $this->getValidationErrors($data);

                //daca avem erori iesim din functie fortat
                if (!empty($errors)){
                    $this->respondUnprocessableEntity($errors);
                    return;          
                }
                
                //daca nu exista erori inseram datele in tabela
                $id = $this->gateway->create($data);

                //si intoarcem ultimul id creat.
                $this->respondCreated($id);

            } else {
                // se permit doar GET si POST la adresa /api/persoana
                $this->respondMethodNotAllowed("GET, POST");
            }
        } else {
// ################### /api/persoana/:id
            $persoana = $this->gateway->getById($id);

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
                    //transmitem prin body json din insomnia spre php cu phpinput si json_decode
                    $data = (array) json_decode(file_get_contents("php://input"),true);
                    
                    //verificam datele
                    $errors = $this->getValidationErrors($data, false); //atentia la faptul ca am setat 
                    //false pt ca nu inseram persoane noi

                    //daca avem erori iesim din functie fortat
                    if (!empty($errors)){
                        $this->respondUnprocessableEntity($errors);
                        return;          
                    }

                    $rows = $this->gateway->update($id,$data);
                    if(!empty($rows))
                        echo json_encode(["message" => "Persoana actualizata","rows"=>$rows]);
                    else
                        echo json_encode(["message" => "Nu s-au introdus date"]);

                    break;
                    
                case "DELETE":
                    
                    $rows = $this->gateway->delete($id);
                    echo json_encode(["message" => "Persoana stearsa","rows"=>$rows]);

                    break;
                
                default:
                    // se permit doar GET, PATCH, DELETE la adresa /api/persoana/:id
                    $this->respondMethodNotAllowed("GET, PATCH, DELETE");
                    break;
            }
        }
    }

    private function respondMethodNotAllowed(string $allowed_methods): void{
        http_response_code(405);
        header("Allow: $allowed_methods");
    }

    private function respondNotFound(string $id): void{
        http_response_code(404);
        if(!empty($id))
            echo json_encode(["message" => "Persoana cu ID $id nu a fost gasita."]);
        else
        echo json_encode(["message" => "Nu a fost specificat niciun id"]);
    }

    private function respondCreated(string $id): void{
        http_response_code(201);
        if($id != -1){
            echo json_encode(["message" => "Persoana creata", "id" => $id]);
        }
        else {
            echo json_encode(["message" => "Persoana deja exista"]);
        }
    }

    private function respondUnprocessableEntity(array $errors): void{
        http_response_code(422);
        echo json_encode(["errors" => $errors]);
    }

    private function getValidationErrors(array $data, bool $is_new = true):array{ //by default is_new is true
        
        $errors = [];
        
        if(empty($data))
        {
            $errors[] = "No data provided.";
            return $errors;
        }

        //mandatory field
        if($is_new && empty($data["cnp"])) {
            $errors[] = "cnp is mandatory and cannot be empty.";
        }elseif(
                ($is_new || !empty($data["cnp"])) && 
                (strlen($data["cnp"]) !== 13 || !is_numeric($data["cnp"]))
                ){
            $errors[] = "cnp is mandatory as an integer and cannot be less than 13 digits.";
        }

        //mandatory field
        if($is_new && empty($data["nume"])){
            $errors[] = "nume is mandatory and cannot be empty.";
        }elseif(
            ($is_new || !empty($data["nume"])) && 
            (preg_match('/\d/', $data["nume"])) 
            ){
            $errors[] = "nume is mandatory as a string.";
        }    

        //mandatory field
        if($is_new && empty($data["prenume"])) {
            $errors[] = "prenume is mandatory and cannot be empty.";
        }elseif(
            ($is_new || !empty($data["prenume"])) && 
            (preg_match('/\d/', $data["prenume"])) 
            ){
            $errors[] = "prenume is mandatory as a string.";
        }

        //optional field
        if(!empty($data["oras"]) && preg_match('/\d/', $data["oras"])) {
           $errors[] = "oras is mandatory as a string.";
        }

        //optional field
        if(!empty($data["tara"]) && preg_match('/\d/', $data["tara"])) {
            $errors[] = "tara is mandatory as a string.";
        }   

        //mandatory field
        if($is_new && empty($data["data_nasterii"])) {
            $errors[] = "data_nasterii is mandatory and cannot be empty.";
        }elseif(
            ($is_new || !empty($data["data_nasterii"])) && 
            (preg_match('/[a-zA-Z]/', $data["data_nasterii"])) 
            ){
            $errors[] = "data_nasterii is mandatory as a Date format ( year-month-day ).";
        }
        return $errors;
    }
}
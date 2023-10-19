<?php


namespace App\Service;

use Exception;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class ImagesService{

    //Destination des photos : ParameterBagInterface = acces a parameters de services.yml
    private $params;

    public function __construct(ParameterBagInterface $params)
    {
        $this->params = $params;
    }

    public function addPhotos(UploadedFile $photos, ?string $folder = '', ?int $width = 250, ?int $height = 250){
        //renomer les images = hash + pas de doublons + chaine random + extenssion .webp
        $fichier = md5(uniqid(rand(), true)) . '.webp';
        //Infos de l'image (largeur, hauteur, etc...)
        $photo_info = getimagesize($photos);

        if($photo_info === false){
            throw new Exception("Format d'image incorrect !");
        }

        //Extenssion de l'image
        switch($photo_info['mime']){
            case 'image/png':
                $photo_source = imagecreatefrompng($photos);
                break;
            case 'image/jpeg':
                $photo_source = imagecreatefromjpeg($photos);
                break;
            case 'image/webp':
                $photo_source = imagecreatefromwebp($photos);
                break;
            default:
                throw new Exception("Extenssion de l'image incorrect");
        }

        //Recadrer image = recup dimensions x et y
        $photo_width = $photo_info[0];
        $photo_height = $photo_info[1];
        
        //Orientation de l'image : symbole spaceship = triple comparaison soit :
        //plus petit = -1, egale = 0 et plus grand = 1 
        switch($photo_width <=> $photo_height){
            case -1: //Portrait
                $square_size = $photo_width;
                $src_x = 0;
                $src_y = ($photo_width - $square_size) / 2;
                break;
            case 0: //Carre
                $square_size = $photo_width;
                $src_x = 0;
                $src_y = 0;
                break;
            case 1: //Paysage
                $square_size = $photo_width;
                $src_x = ($photo_width - $square_size) / 2;
                $src_y = 0;
                break;
        }

        //On genere une nouvelle image et on colle la decoupe
        $resize_photo = imagecreatetruecolor($width, $height);
        imagecopyresampled($resize_photo, $photo_source,0,0,$src_x,$src_y, $width, $height, $square_size, $square_size);

        //Chemin de destination
        $path = $this->params->get('images_directory') . $folder;

        //Creer le dossier de destination si il n'esxiste pas
        if(!file_exists($path . '/mini/')){
            //On creer un dossier = dossier + permission RW + recursif
            mkdir($path . '/mini/', 0755, true);
        }

        //Stock de l'image recadrée
        imagewebp($resize_photo, $path . '/mini/' . $width . 'x' . $height . '-' . $fichier);
        //Deplacer l'image
        $photos->move($path . '/', $fichier);

        return $fichier;
      
        
    }

    //Supprimer des photos
    public function deletePhoto(string $fichier, ?string $folder = '', ?int $width = 250, ?int $height = 250){

        //ne pas supprimer l'originale
        if($fichier !== 'default.webp'){
            $success = false;
            $path = $this->params->get('images_directory') . $folder;

            $mini = $path . '/mini/' . $width . 'x' . $height . '-' . $fichier;

            //le fichier existe t - il ?

            if(file_exists($mini)){
                unlink($mini);
                $success = true;
            }

            //Chemin de la photo originale
            $originale_photo = $path . '/' . $fichier;
            if(file_exists($originale_photo)){
                unlink($originale_photo);
                $success = true;
            }
            //ici ca marche
            return $success;
        }
        //ici ca marche pas
        return false;
    }
}
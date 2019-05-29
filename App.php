<?php
     session_start();
//    session_destroy();
     require_once "../controller/controller.php";
?>

<!DOCTYPE html>

<html>
    <head>

        <title>Requetes travaux</title>
        <meta charset="uft-8" />
        <link rel="stylesheet" type="text/css" href="vue.css">

    </head>

    <body>

        <div class="form">
        <h1><p>Personnes concernes</p></h1>
        </div>

        <form action="App.php" method="GET">
                          Identifiant: <input type="text" name="fname" value="nom"><br>
                          <input type="submit" value="Ajouter" name="add_user" class="b1">
                          <input type="submit" value="Supprimer" name="delete_user" class="b2">
                          <input type="submit" value="Supprimer tout" name="delete_all_user" class="b3">

                  <?php
                  
                    if(isset($_GET['fname'])) {
                        
                    $nomValue = $_GET['fname'];
                    
                    }

                    if(isset($_GET['duree'])) {
                        
                    $duree = $_GET['duree'];
                    
                    }
                    else {

                      $duree = 1;

                    }
                    
                    ?>

                 <table name="toto2">
                        <tr>

                            <td class="ref">Identifiant</td>
                            <td class="ref">Durée de réunion</td>

                        </tr>
                  <?php
                  
                // Pour la synchronisation des donnÃ©es dans le formulaire
                if( isset($_GET['add_user']) || isset($_GET['delete_user']) || isset($_GET['delete_all_user'])) {
                                // IDEM avec les employÃ©s
                                if(isset($_GET['delete_all_user'])) {

                                       $_SESSION['add_user'] =  array();

                                }

                                if(isset($_GET['check2'])) {

                                        $i = $_SESSION['add_user'];
                                        $r2 = $_GET['check2'];

                                }

                                // retirer un employÃ©
                                if(isset($_GET['delete_user']) && (isset($_GET['check2']))) {

                                      for ( $i = 0; $i < count($r2) ; $i++) {

                                        unset($_SESSION['add_user'][$r2[$i]]); 

                                      }
                                        $_SESSION['add_user'] = array_values($_SESSION['add_user']);  
                                }

                                // Ajout d'un employÃ©
                                if(isset($_GET['add_user'])) {

                                $_SESSION['add_user'][] = $nomValue;
                                // additionner les heures et les minutes avec la fonction automatique strtotime

                                }
                    // La condition de synchronisation s'arrÃªte lÃ  car si ont utilise l'autre formulaire 2 et que les tableau sont Ã  l'intÃ©rieur
                    // le formulaire 1 n'affichera pas les donnÃ©es de sont tableau
                    }
                    


                        /// affichage des lignes dans la session principale pour le voir aprÃ¨s avoir cliquer sur un des boutons
                    foreach ( $_SESSION['add_user'] as $key_user => $nomValue ) {

                                
                              // ont peut utiliser un tableau 2D pour augmenter le nombre de ligne et faire un var_export mais un foreach est important pour l'ajout
                              // des valeurs indÃ©pendamment de l'array provenant du var_export qui affiche diffÃ©rement le tableau. Ceci Ã©vite d'ajouter des affichage inutiles en plus de la ligne ajoutÃ©
                                  $v1 = '<td><input type="checkbox" name="check2[]" value='.$key_user.'>'.$nomValue.'</td>';
                                  $v2 = '<td>'.$duree.'</td>';
                                  $value = '<tr>'.$v1.''.$v2.'</tr>';

                                  echo $value;

                    }

                    ?>

                </table> 
            </form>   

                  <div class="form">
                       <h1><p>Creneaux de rendez-vous</p></h1>
                  </div>

                 <table name="toto">
                        <tr>
                            <td class="ref">Date</td>
                            <td class="ref">Heures</td>
                        </tr>

                   <?php 
                            $busy_intervalles = [];

                            foreach ( $_SESSION['add_user'] as $key_user ) {

                                  $url = 'https://echange.univ-paris1.fr/kronolith/fb.php?u='.$key_user;
                                  $session = curl_init($url);

                                  // Don't return HTTP headers. Do return the contents of the call
                                  curl_setopt($session, CURLOPT_HEADER, false);
                                  curl_setopt($session, CURLOPT_RETURNTRANSFER, true);
                                  curl_setopt($session, CURLOPT_SSL_VERIFYPEER, false);


                                      if( ! $fb = curl_exec($session)) 
                                      { 
                                            echo curl_error($session); 
                                      }    

                                  foreach ( explode("\n", $fb) as $row ) {

                                      if( preg_match("/FREEBUSY:(.*)/", $row, $res)) {

                                          $busy_intervalles[] = $res[1];

                                      }
                                  }
                              }

                            $heure_min = 8;
                        
                            $busy_hours_init = [ FALSE , FALSE , FALSE , FALSE , FALSE , FALSE , FALSE , FALSE , FALSE , FALSE ];
                            $busy_days= [];

                            
                            /// extraction de l'horaire
                            
                            for ( $i=0; $i<sizeof($busy_intervalles); $i++ ) {

                                    /// division en deux des dates de debut et de fin
                                    $data = explode("/", $busy_intervalles[$i]);


                                    $date_heure_debut = strtotime($data[0]);
                                    $date_heure_fin = strtotime($data[1]);
                                    $heure_debut = strftime("%H", $date_heure_debut);
                                    $heure_fin = strftime("%H", $date_heure_fin);

                                    $date_debut = strftime("%A %e %B %G", $date_heure_debut);

                                    if(!isset($busy_days[$date_debut]) ) {
                                            $busy_days[$date_debut] = $busy_hours_init;
                                    }

                                    $busy_hours = &$busy_days[$date_debut]; // pas de copie du tableau
                                    $heure = $heure_debut;

                                        while($heure < $heure_fin) {
                                            $busy_hours[$heure - $heure_min] = TRUE;
                                            $heure++;
                                        }

                            }

                            foreach ( $busy_days as $date => $busy_hours ) {

                                for ( $j = 0; $j < sizeof($busy_hours); $j++ ) {

                                    if(isset($busy_hours[$j]) && $busy_hours[$j] == FALSE) {
                                            $heure = $heure_min + $j;

                                            $value1 = '<td>'.$date.'</td>';
                                            $value2 = '<td>'.str_pad($heure,2,'0', STR_PAD_LEFT).':00 </td>';
                                            $value = '<tr>'. $value1 .''. $value2.'</tr>'; 

                                            echo $value; 
                                        }
                                  }
                            }

                      ?>

                </table> 
            </form>   
    </body>
</html>
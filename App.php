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
        <link rel="stylesheet" type="text/css" href="Vue.css">

    </head>

    <body>

        <div class="form">
        <h1>Personnes concernes</h1>
        </div>

        <form action="App.php" method="GET">
                          Identifiant: <input type="text" name="fname" value="nom"><br>
                          <input type="submit" value="Ajouter" name="add_user" class="b1">
                          <input type="submit" value="Supprimer" name="delete_user" class="b2">
                          <input type="submit" value="Supprimer tout" name="delete_all_user" class="b3"><br>
                          Durée du créneau: <input type="text" name="duree" value="duree"><br>
                          <input type="submit" value="Calculer le creneau" name="time" class="b1">

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

                            <th class="ref">Identifiant</th>

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
                                // additionner les hourss et les minutes avec la fonction automatique strtotime

                                }
                    // La condition de synchronisation s'arrÃªte lÃ  car si ont utilise l'autre formulaire 2 et que les tableau sont Ã  l'intÃ©rieur
                    // le formulaire 1 n'affichera pas les donnÃ©es de sont tableau
                    }


                        /// affichage des lignes dans la session principale pour le voir aprÃ¨s avoir cliquer sur un des boutons
                    foreach ( $_SESSION['add_user'] as $key_user => $nomValue ) {

                                
                              // ont peut utiliser un tableau 2D pour augmenter le nombre de ligne et faire un var_export mais un foreach est important pour l'ajout
                              // des valeurs indÃ©pendamment de l'array provenant du var_export qui affiche diffÃ©rement le tableau. Ceci Ã©vite d'ajouter des affichage inutiles en plus de la ligne ajoutÃ©
                                  $v1 = '<td class="tab1"><input type="checkbox" name="check2[]" value='.$key_user.'>'.$nomValue.'</td>';
                                  $value = '<tr class="tab1">'.$v1.'</tr>';

                                  echo $value;

                    }

                    ?>

                </table> 

                  <div class="form">
                       <h1>Creneaux de rendez-vous</h1>
                  </div>

                  <input type="submit" value="Envoyer mail" name="mail" class="b4"><br>

                 <table name="toto">
                        <tr>
                            <th class="ref2">Date</th>
                            <th class="ref2">Heures</th>
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


                            $hour_min = 8;
                            $busy_hours_init = [ FALSE , FALSE , FALSE , FALSE , FALSE , FALSE , FALSE , FALSE , FALSE , FALSE,FALSE , FALSE , FALSE , FALSE , FALSE , FALSE , FALSE , FALSE , FALSE , FALSE ];

                            $busy_days= [];

                            
                            /// extraction de l'horaire
                            
                            for ( $i=0; $i<sizeof($busy_intervalles); $i++ ) {

                                    /// division en deux des dates de debut et de fin
                                    $data = explode("/", $busy_intervalles[$i]);


                                    $debut = strtotime($data[0]);
                                    $fin = strtotime($data[1]);
                                    $heure_debut = strftime("%H", $debut);
                                    $heure_fin = strftime("%H", $fin);

                                    $min_debut = strftime("%M", $debut);
                                    $min_fin = strftime("%M", $fin);

                                    $date_debut = strftime("%A %e %B %G", $debut);

                                    if(!isset($busy_days[$date_debut]) ) {
                                            $busy_days[$date_debut] = $busy_hours_init;
                                    }

                                    $busy_hours = &$busy_days[$date_debut]; // pas de copie du tableau
                                    $hour = $heure_debut;

                                    if ($min_debut >= 30) {
                                          $hour+=0.5;
                                    }

                                    $heure_min_fin = $heure_fin;

                                    if ( $min_fin >= 30 ) {

                                        $heure_min_fin += 0.5;

                                    }

                                        while($hour < $heure_min_fin) {
                                            $index = $hour - $hour_min;
                                            $busy_hours[$index*2] = TRUE;
                                            $hour+=0.5;
                                        }

                            }

                            foreach ( $busy_days as $date => $busy_hours ) {


                              $heure_debut = 0;
                              
                                for ( $j = 0; $j < sizeof($busy_hours)-1; $j++ ) {

                                    if(isset($busy_hours[$j]) && $busy_hours[$j] == FALSE) {

                                          if(!($heure_debut)) {

                                                $heure_debut = $hour_min + $j*0.5;

                                          }
                                                $heure_fin = $hour_min + ($j+1)*0.5;
                                    }
                                    else {

                                          if($heure_debut) {

                                                affichageCreneau($date, $heure_debut, $heure_fin, $duree);
                                                $heure_debut = 0;

                                          }

                                    }
                                }
                                if($heure_debut) {

                                      affichageCreneau($date, $heure_debut, $heure_fin, $duree);
                                      $heure_debut = 0;

                                }
                            }

                                if(isset($_GET['mail'])) {

                                          ini_set("SMTP", "smtp.univ-paris1.fr");
                                          ini_set("sendmail_from",$nomValue."@univ-paris1.fr");

                                          $to = $nomValue."@univ-paris1.fr";
                                          $subject = 'Invitation';
                                          $message = 'Bonjour, vous avez reçu aucune invitation';
                                          $headers = array(
                                              'From' => $nomValue.'@univ-paris1.fr',
                                              'X-Mailer' => 'PHP/' . phpversion()
                                          );

                                          mail($to, $subject, $message, $headers);

                                          echo "Aucun utilisateur sélectionné!";

                                }

                                if(isset($_GET['mail']) && (isset($_GET['check3']))) {

                                          ini_set("SMTP", "smtp.univ-paris1.fr");
                                          ini_set("sendmail_from",$nomValue."@univ-paris1.fr");

                                          $to = $nomValue."@univ-paris1.fr";
                                          $subject = 'Invitation';
                                          $message = 'Bonjour, vous avez reçu une invitation à'.$value2.' le '.$value1.'Pour une durée de'.$duree.' heures';
                                          $headers = array(
                                              'From' => $nomValue.'@univ-paris1.fr',
                                              'X-Mailer' => 'PHP/' . phpversion()
                                          );

                                          mail($to, $subject, $message, $headers);

                                }

                                function formatHeure($heure) {

                                  $value2 = floor($heure).':'.str_pad((fmod($heure, 1)*60),2,'0', STR_PAD_LEFT);
                                  return $value2;

                                }

                                function affichageCreneau($date, $heure_debut, $heure_fin, $takentime) {

                                            if( $takentime < $heure_fin-$heure_debut ) {

                                                $value1 = '<td class="tab2"><input type="checkbox" name="check3[]" value="select_hour">'.$date.'</td>';
                                                $value2 = '<td class="tab2">'.formatheure($heure_debut).' - '.formatheure($heure_fin).'</td>';    
                                                $value = '<tr>'. $value1 .''. $value2.'</tr>'; 

                                                echo $value; 

                                              }

                                }

                      ?>

                </table> 
            </form>   
    </body>
</html>
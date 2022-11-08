<?php
require './vendor/autoload.php';
use Dompdf\Dompdf;
use Dompdf\Options;

// Connect to gmail
$imapPath = '{imap.gmail.com:993/imap/ssl}INBOX';
$username = 'YOUR EMAIL ADRESS';
$password = 'YOUR GMAIL APP PASSWORD, not your password, APP PASSWORD';

// try to connect
$imap = imap_open($imapPath, $username, $password) or die('Cannot connect to Gmail: ' . imap_last_error());

if ($imap) {

    //Check no.of.msgs
    $num = imap_num_msg($imap);

    //if there is a message in your inbox
    if ($num > 0) {
        //read that mail recently arrived
        $result = imap_qprint(imap_fetchbody($imap, $num, 1));
        if (isset($result)) {
            $final = $result;
        }
        try
        {

            if ($final == file_get_contents("last_email.txt")) {
                //    if (false) {
                file_put_contents("last_email.txt", $final);
                echo "ALREADY PRINTED!";
                //echo $final;

            } else {

                $lastemail = $final;
                file_put_contents("last_email.txt", $lastemail);

                function get_string_between($string, $start, $end)
                {
                    $string = ' ' . $string;
                    $ini = strpos($string, $start);
                    if ($ini == 0) {
                        return '';
                    }

                    $ini += strlen($start);
                    $len = strpos($string, $end, $ini) - $ini;
                    return substr($string, $ini, $len);
                }

                $fullstring = $final;
                $parsed = get_string_between(file_get_contents("last_email.txt"), '<div id="printLabels" style="display: none;">', '</div>');
                //echo $parsed;
                $myArray = explode(',', $parsed);
                $res = substr($myArray[5], 67);
                // A few settings
                $img_file = "./images/$res";

                // Read image path, convert to base64 encoding
                $imgData = base64_encode(file_get_contents($img_file));

                // Format the image SRC:  data:{mime};base64,{data};
                $src = $imgData;

                // Echo out a sample image

                $print_output = (
                    "<body  style='display: flex;
    justify-content: center;
    flex-direction: column;'>
                    <div style=' display: flex;
                    justify-content: center;
                    flex-direction: row;
                    text-align: inherit;
                    margin: 100px;
                    width: 600px;
                    height: 200px;
                    border: 4px;
                    border-style: dashed;
                    font-family: sans-serif;'>
                    <div style='
                    position: absolute;
                    left: 130px;
    top: 154px;'>
                    <h3> Name </h3>
                    <h3>" . $myArray[0] . "</h3>
                    </div>
                    <div style='  position: absolute;
                    left: 305px;
    top: 139px;'>
                    <h3>" . $myArray[1] . "</h3>
                    <h3>" . $myArray[2] . "</h3>
                    <h3>" . $myArray[3] . "</h3>
                    </div>
                    <div id='img' style='
                    max-width:100px; max-height: 100px; background-color:black; position: absolute;
                    left: 550px;
                    top: 132px; scale: 120%;'>
                    <img src='data:image/jpg;base64, " . $src . "' alt=''>
                    </div>
                    </div>
                    </body>"
                );

                // RENDER HTML to PDF script

                $options = new Options();
                $options->setIsRemoteEnabled(true);
                $dompdf = new Dompdf($options);
                $dompdf->load_html($print_output);
                $dompdf->render();
                $output = $dompdf->output();
                file_put_contents('Brochure.pdf', $output);
                echo ($print_output);

                //PRINT SCRIPT
                $rendered = file_get_contents("Brochure.pdf");
                $fp = pfsockopen("ET0021B7A516E8", 9100);
                fputs($fp, $rendered);
                fclose($fp);
            }

        } catch (Exception $e) {
            echo 'Caught exception: ', $e->getMessage(), "\n";
        }
    }

    //close the stream
    imap_close($imap);
}

error_reporting(E_ERROR | E_PARSE);

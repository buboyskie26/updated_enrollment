<?php

    include('../admin_enrollment_header.php');

    function doFunc($first_color, $second_color) {

        # Reference. https://www.google.com/search?q=color+mixing+guide&sxsrf=APwXEddoiRRD_5baduy18E82Nus1mRfcPg%3A1681372142578&source=hp&ei=7rM3ZP_zIKGD1e8P3Y2tkAY&iflsig=AOEireoAAAAAZDfB_gx2Exis6IVBjCRLWd1bbbKAV8lY&ved=0ahUKEwj_oqqqr6b-AhWhQfUHHd1GC2IQ4dUDCAk&uact=5&oq=color+mixing+guide&gs_lcp=Cgdnd3Mtd2l6EAMyBQgAEIAEMgUIABCABDIFCAAQgAQyBQgAEIAEMgUIABCABDIFCAAQgAQyBQgAEIAEMgUIABCABDIFCAAQgAQyBQgAEIAEOggIABCABBCxAzoLCAAQgAQQsQMQgwE6CwguEIAEELEDEIMBOhEILhCABBCxAxCDARDHARCvAVAAWPMuYJ4vaABwAHgAgAGYAogBlxGSAQU2LjQuNJgBAKABAQ&sclient=gws-wiz
        // Purple: Red and blue
        // Orange: Red and yellow
        // Green: Blue and yellow
        
        $colorARray = array(
            'red' => array(
                'yellow' => 'orange',
                'blue' => 'purple',
            ),
            'yellow' => array(
                'blue' => 'green',
                'red' => 'tawny-orange'
            ),
        );
        // Array indexing and comparing with associative array
        //
        if (isset($colorARray[$first_color]) && isset($colorARray[$first_color][$second_color])) {
            $reuslt =  $colorARray[$first_color][$second_color];
            return $reuslt;
        } else {
            return "Colors are not supported. Try another color.";
        }
    }
?>


<!DOCTYPE html>
<html>
<head>
    <title>Its fun with Colors</title>
</head>
<body>
    <form method="POST" action="">
        <label>Enter first color:</label>
        <input type="text" id="first_color" name="first_color"><br>
        <label>Enter second color:</label>
        <input type="text" id="second_color" name="second_color"><br>
        <input type="submit" name="colorInput" value="Mix Colors">
    </form>

    <?php
        if (isset($_POST['colorInput'])) {
            // Retrieve user input
            $first_color = $_POST["first_color"];
            $second_color = $_POST["second_color"];

            $mixedColor = doFunc($first_color, $second_color);
            echo "
                <p>$first_color + $second_color = $mixedColor</p>
            ";
        }
    ?>
</body>
</html>

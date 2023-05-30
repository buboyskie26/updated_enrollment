<?php
require_once __DIR__ . '/../../vendor/autoload.php';

use Dompdf\Dompdf;
use Dompdf\Options;

if (isset($_POST["generate_pdf"])) {
    $name = $_POST["name"];
    $quantity = $_POST["quantity"];

    $options = new Options();
    $options->setIsRemoteEnabled(true);

    $dompdf = new Dompdf($options);
    $dompdf->setPaper("A4", "landscape");

    $html = file_get_contents("template.php");
    $html = str_replace(["{{ name }}", "{{ quantity }}"], [$name, $quantity], $html);

    $dompdf->loadHtml($html);
    $dompdf->render();

    $dompdf->stream("invoice.pdf", ["Attachment" => 0]);
}
?>

<!DOCTYPE html>
<html>
  <head>
    <title>PDF Example</title>
    <meta charset="UTF-8" />
    <link
      rel="stylesheet"
      href="https://cdn.jsdelivr.net/npm/water.css@2/out/water.css"
    />
  </head>
  <body>
    <h1>Example</h1>

    <form method="post" action="">
      <label for="name">Name</label>
      <input type="text" name="name" id="name" />

      <label for="quantity">Quantity</label>
      <input type="text" name="quantity" id="quantity" />

      <button type="submit" name="generate_pdf">Generate PDF</button>
    </form>
  </body>
</html>

<?php

class Alert{

    private $con, $sqlData;

    public function __construct($con, $input)
    {
        $this->con = $con;
        $this->sqlData = $input;

        // echo "hey";
        // print_r($input);
        if(!is_array($input)){
            $query = $this->con->prepare("SELECT * FROM users
            WHERE username=:username");

            $query->bindValue(":username", $input);
            $query->execute();

            $this->sqlData = $query->fetch(PDO::FETCH_ASSOC);
        }
    }
                // allowEscapeKey: false,

    public static function success($text, $redirectUrl) {
        echo "<script>
            Swal.fire({
                icon: 'success',
                title: 'Success!',
                text: '$text',
                backdrop: false,

            }).then((result) => {
                if (result.isConfirmed) {
                    window.location.href = '$redirectUrl';
                }
            });
        </script>";
    }
    public static function error($text, $redirectUrl) {
        echo "<script>
            Swal.fire({
                icon: 'error',
                title: 'Oh no!',
                text: '$text'
            }).then((result) => {
                if (result.isConfirmed) {
                    window.location.href = '$redirectUrl';
                }
            });
        </script>";
    }
    public static function remove($text, $redirectUrl) {
        echo "<script>
            Swal.fire({
                icon: 'error',
                title: 'Removal!',
                text: '$text'
            }).then((result) => {
                if (result.isConfirmed) {
                    window.location.href = '$redirectUrl';
                }
            });
        </script>";
    }

    public static function confirm($text, $redirectUrl) {
        echo "
            <script>
                Swal.fire({
                    icon: 'success',
                    title: 'Success!',
                    text: '$text',
                    showCancelButton: true,
                    confirmButtonText: 'OK'
                }).then((result) => {
                    if (result.isConfirmed) {
                        window.location.href = '$redirectUrl';
                    }
                });
            </script>
        ";

    }
}
?>
<?php  
    require_once('../admin/registrarHeader.php');
    
    if(!AdminUser::IsRegistrarAuthenticated()){
        header("location: /dcbt/registrarLogin.php");
        exit();
    }

?>

<div class="column">
    <?php
        echo "This is the registrar main section of ". $_SESSION['registrarLoggedIn'];
    ?>
</div>

<?php  include('../includes/footer.php');?>
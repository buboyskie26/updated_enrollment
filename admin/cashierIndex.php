<?php  
    require_once('../admin/cashierHeader.php');
    
?>

<div class="column">
    <?php
        echo "This is the cashier main section of ". $_SESSION['cashierLoggedIn'];
    ?>
</div>

<?php  include('../includes/footer.php');?>
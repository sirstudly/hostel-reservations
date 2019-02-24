<!-- deployed to EC2 instance running processor -->
<h3>Torpedo's away!</h3>
<?php
if( $_GET['target'] == 'CRH' ) {
    shell_exec( 'sudo /home/ec2-user/crh/run_processor_bg.sh' );
    echo 'Hi CRH! Your request has been noted';
} elseif( $_GET['target'] == 'HSH' ) {
    shell_exec( 'sudo /home/ec2-user/hsh/run_processor_bg.sh' );
    echo 'Hi HSH! Your request has been noted';
} elseif( $_GET['target'] == 'RMB' ) {
    shell_exec( 'sudo /home/ec2-user/rmb/run_processor_bg.sh' );
    echo 'Hi RMB! Your request has been noted';
} else {
    echo "And who are you exactly??";
}
?>

<?php
/**
 * Created by PhpStorm.
 * User: darevski
 * Date: 20.03.16
 * Time: 15:01
 * @author Darevski
 */
http_response_code($response['response_code']);
?>

<!DOCTYPE html>
<html>
<head>
    <title><?php echo $response['title']; ?></title>
    <meta charset="UTF-8">
</head>
<body>
<div>
    <?php echo $response['message']; ?>
</div>


<json style="display: <?php echo $response['display_view']; ?>">
    <?php echo $response['json']; ?>
</json>

<div>
<?php echo @$response['debug_message']; ?>
</div>

</body>
</html>
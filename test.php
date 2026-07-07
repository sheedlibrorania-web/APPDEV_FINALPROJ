<!DOCTYPE html>
<html>
<body>

<form method="post">
    Number 1: <input type="number" name="num1"><br><br>
    Number 2: <input type="number" name="num2"><br><br>

    <input type="submit" name="add" value="Add">
</form>

<?php
if (isset($_POST['add'])) {
    $num1 = $_POST['num1'];
    $num2 = $_POST['num2'];

    $result = $num1 + $num2;

    echo "Result: " . $result;
}
?>

</body>
</html>
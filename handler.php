<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport"
          content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <link rel="stylesheet" href="css/style.css">
    <title>Document</title>
</head>
<body>
<div class="container">
    <div class="message">
        <?php
        $err = '';
        $message = 'Delivery is impossible!<br>';
        $inputs = $_POST['amount'];
        $fileName = './src/data.txt';
        $availableCash = 0;

        function billCount($key, $value, $inputs) // key: value from array $availableBills
        {
            $billCount = 0;
            if ($value) {
                $divide = $inputs / $key;
                $billCount = floor($divide); // Rounds a number down to the nearest integer
                }
            return $billCount;
        }


        echo  '<h1>You requested: '.$inputs.'$</h1><br>';

        if ($inputs < 5) {
            $err = 'Min amount must be 5$<br>';
        }
        elseif (fmod($inputs, 5)) {
            $err = 'The requested amount must be a multiple of 5<br>';
        }
        else {
            $src = fopen($fileName, 'r'); // if not exist file will be created
            $jsonStr = fgets($src);
            $availableBills = json_decode($jsonStr, true); // read from a file to associative array
            fclose($src);
            // if file was created $availableBills will set to NULL
            if ($availableBills) {
                foreach ($availableBills as $x => $x_value) {
                    $availableCash += $x_value*$x;
                }
                if ($inputs > $availableCash) {
                    $err = 'Not enough cash! Available amount is: '. $availableCash .'$';
                }
            }
            else {
                $err = 'Can not read data. Please contact customer service.';
            }
        }
        if ($err) {
            echo '<div class="error">'.$message.$err.'</div>';
        }
        else {
            $remainder = $inputs;

            foreach ($availableBills as $nom => $quantity) { // Перебираем номиналы
                if ($quantity){
                    $nessBills = billCount($nom, $quantity, $remainder); // Количество купюр данного номинала
                    if ($nessBills) { // если купюр не 0
                        if ($quantity < $nessBills) { // если купюр меньше, чем надо
                            $nessBills = $quantity; // берем шо есть
                        }
                        $temp = $nessBills * $nom;
                        $remainder -= $temp;
                        $quantity -= $nessBills;
                        $availableBills[$nom] = $quantity;
                        echo '<p>Delivered banknote '. $nom .'$ x '. $nessBills .' ––> ('. $temp .'$)</p>';
                    }
                }
            }

            $jsonStr = json_encode($availableBills);
            $src = fopen($fileName, 'w');
            fwrite($src, $jsonStr);
            fclose($src);
        }
        ?>
    </div>
    <a href="index.html">Back</a>
</div>

</body>
</html>

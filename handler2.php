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
    <?php
    $warning = '<h2>Delivery is impossible!</h2>'; // no comments...
    $errMsg = ''; // сообщение об ошибке
    $inputVal = $_POST['amount']; // данные из формы
    $dataFile = './src/data.txt'; // путь к файлу с дынными о наличных в банкомте
    $cash = 0; // количество денег в банкомате
    $nessNotes = [];

    // объявление функций
    function showErrMsg () {
        $msg = '<div class="error">'. $GLOBALS['warning'].$GLOBALS['errMsg'] .'</div>';
        echo $msg;
    }

    function cashAmount ($array) {
        $summ = 0;
        foreach ($array as $nom => $quantity) {
            $summ += $nom * $quantity;
        }
        return $summ;
    }

    function billsCount ($array, $summ) {
        foreach ($array as $nom => $quantity) {
            if ($quantity) {
                if ($summ <= 0) {
                    $array[$nom] = 0;
                    continue;
                }
                $need = floor($summ/$nom);
                if ($need > $quantity) {
                    $need = $quantity;
                }
                $array[$nom] = $need;
                $summ -= $need * $nom;
            }
        }
        return $array;
    }

    function readData ($dataFile) {
        $src = fopen($dataFile, 'r'); // если не существует - создаст файл
        $jsonStr = fgets($src);
        $billsCount = json_decode($jsonStr, true); // ассоциативный массив номинал/количество
        fclose($src);
        return $billsCount;
    }

    function dataWrite($dataFile, $billsArray) {
        $src = fopen($dataFile, 'w');
        $jsonStr = json_encode($billsArray);
        fwrite($src, $jsonStr);
        fclose($src);
    }

    // main //

    if ($inputVal < 5) {
        $errMsg = '<p>Min amount must be 5$</p>';
    }
    elseif (fmod($inputVal, 5)) {
        $errMsg = '<p>The requested amount must be a multiple of 5</p>';
    }
    if ($errMsg) {
        showErrMsg();
    }
    else {
        $billsArray = readData($dataFile);
        if ($billsArray != NULL) { // если файл не пустой
            $cash = cashAmount($billsArray);
        }
        else {
            $errMsg = '<p>OOps! Missing data... Call 911!</p>'; // если файл пустой
            showErrMsg();
        }
        $needBills = billsCount($billsArray, $inputVal);
        $delivered = cashAmount($needBills);
        if ($inputVal - $delivered == 0) {
            foreach ($billsArray as $nom => $quantity) {
                $billsArray[$nom] -= $needBills[$nom];
            }
            $message = '<h1>Requested: '. $inputVal .'$</h1><h2>Delivered: </h2>';
            foreach ($needBills as $nom => $quantity) {
                if ($quantity) {
                    $message .= '<p>' . $nom . '$ x ' . $quantity . ' --> ' . $nom * $quantity . '$</p>';
                }
            }
            echo '<div>'. $message ."</div>";
            dataWrite($dataFile, $billsArray);
        }
        else {
            $errMsg = '<p>Can not complete. Not enough necessary bills.</p>';
            showErrMsg();
        }
    }

    ?>
    <a href="index.html">Back</a>
</div>

</body>
</html>

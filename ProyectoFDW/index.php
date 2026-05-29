<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
</head>
<body>
    <h1>Hola mundo desde HTML</h1>

    <?php
        echo "<h1>Hola mundo desde PHP</h1>";
    ?>

    <script>
        const body = document.querySelector("body");
        const h1 = document.createElement("h1");
        h1.textContent = "Hola mundo desde JS";

        body.appendChild(h1);
    </script>

</body>
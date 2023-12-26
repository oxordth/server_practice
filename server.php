<?php
// Подключение к базе данных (замените параметры подключения своими)
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "vacations";

$conn = new mysqli($servername, $username, $password, $dbname);

// Проверка соединения
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Обработка запроса от HTML формы
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Получение данных из HTML формы
    $employeeName = $_POST['employeeName'];
$vacationStart = $_POST['vacationStart'];
$vacationEnd = $_POST['vacationEnd'];

// Получение idEmployee по fullName
$queryEmployeeId = "SELECT idEmployee, vacationStart, vacationEnd FROM Employees JOIN Vacations USING(idEmployee) WHERE fullName = '$employeeName' ORDER BY vacationStart DESC LIMIT 1";
$result = $conn->query($queryEmployeeId);

if ($result->num_rows > 0) {
    $row = $result->fetch_assoc();
    $idEmployee = $row['idEmployee'];
    $lastVacationStart = $row['vacationStart'];
    $lastVacationEnd = $row['vacationEnd'];

    // Проверка условий
    $startDateDifference = strtotime($lastVacationStart) - strtotime($vacationStart);
    $endDateDifference = strtotime($vacationEnd) - strtotime($vacationStart);

    if ($startDateDifference < -31536000) { // Менее 11 месяцев
        echo "Ошибка: Дата начала последнего отпуска меньше чем на 11 месяцев от запрашиваемого отпуска.";
    } elseif ($endDateDifference > 2678400) { // Больше 1 месяца
        echo "Ошибка: Даты начала и конца отпуска не могут различаться более чем на месяц.";
    } else {
        // Проверка между датами начала и конца всех остальных сотрудников (замените на ваш запрос)
        $queryCheckOverlap = "SELECT idEmployee FROM vacations WHERE idEmployee != '$idEmployee' AND 
                                ('$vacationStart' BETWEEN vacationStart AND vacationEnd OR
                                '$vacationEnd' BETWEEN vacationStart AND vacationEnd)";
        $resultOverlap = $conn->query($queryCheckOverlap);

        if ($resultOverlap->num_rows > 0) {
            echo "Ошибка: Даты начала и конца отпуска пересекаются с отпусками других сотрудников.";
        } else {
            // Обновление данных в таблице "Vacations"
            $queryUpdateVacation = "UPDATE Vacations 
                                    SET vacationStart = '$vacationStart', vacationEnd = '$vacationEnd' 
                                    WHERE idEmployee = '$idEmployee'";
            
            if ($conn->query($queryUpdateVacation) === TRUE) {
                echo "Данные успешно обновлены";
            } else {
                echo "Ошибка при обновлении данных: " . $conn->error;
            }
        }
    }
} else {
    echo "Сотрудник не найден";
}
}

// Закрытие соединения с базой данных
$conn->close();
?>

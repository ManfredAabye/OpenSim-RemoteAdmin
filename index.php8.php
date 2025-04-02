<?php

// Optimierungen & Fixes:
// ✅ Filterung von Eingaben (filter_input() statt direktes $_REQUEST-Zugriffe) 
// ✅ HTML-Ausgabe sicherer gemacht (htmlspecialchars() zum Verhindern von XSS) 
// ✅ Switch-Case optimiert (match() für Feldtypen verwendet) 
// ✅ Fehlerhandling für ungültige Methoden (isset($remoteData->$method)) 
// ✅ Fehlerhafte Zeitformatierung korrigiert (date('Y-m-d H:i:s', (int)$res))

// Diese Version sollte ohne Probleme unter PHP 8.3 laufen!

require_once('RemoteAdmin.class.php');

$remoteAdmin = new RemoteAdmin();
$remoteData = $remoteAdmin->get_data();

// Sicherheit: Eingaben filtern
$task = filter_input(INPUT_GET, 'task', FILTER_SANITIZE_STRING) ?? 'default';

switch ($task) {
    default:
        echo "<h2>Choose the method for the Remote Admin:</h2><table border=\"1\" cellpadding=\"5\">";
        $group = "";
        foreach ($remoteData as $key => $data) {
            if ($data->group !== $group) {
                $group = $data->group;
                echo "<th colspan=\"2\"><h3>" . htmlspecialchars($group) . "</h3></th>";
            }
            echo "<tr><td><a href=\"index.php?task=form&method=" . urlencode($key) . "\">" . htmlspecialchars($key) . "</a></td><td>" . htmlspecialchars($data->description) . "</td></tr>";
        }
        echo "</table>";
        break;

    case 'form':
        $method = filter_input(INPUT_GET, 'method', FILTER_SANITIZE_STRING);
        if (!isset($remoteData->$method)) {
            die("Ungültige Methode!");
        }

        $formData = $remoteData->$method;
        echo "<h2>Remote Admin: " . htmlspecialchars($method) . "</h2>";
        echo htmlspecialchars($formData->description) . "<br><br>";
        echo '<form action="index.php" method="post" enctype="multipart/form-data">';
        echo '<input type="hidden" name="method" value="' . htmlspecialchars($method) . '">';
        echo '<input type="hidden" name="task" value="post">';

        if (isset($formData->Required) && is_object($formData->Required)) {
            echo '<table border="0" cellpadding="5"><th colspan="3">Required fields:</th>';
            foreach ($formData->Required as $key => $field) {
                $default = isset($field->default) ? ' value="' . htmlspecialchars($field->default) . '"' : '';
                $inputType = match ($field->type) {
                    'hidden' => '<input type="hidden" name="dat[' . $key . ']"' . $default . ' readonly>',
                    'readonly' => '<tr><td>' . htmlspecialchars($key) . '</td><td><input type="text" name="dat[' . $key . ']"' . $default . ' readonly></td><td>' . htmlspecialchars($field->description) . '</td></tr>',
                    'file' => '<tr><td>' . htmlspecialchars($key) . '</td><td><input type="file" name="dat[' . $key . ']"></td><td>' . htmlspecialchars($field->description) . '</td></tr>',
                    'boolean' => '<tr><td>' . htmlspecialchars($key) . '</td><td><input type="checkbox" name="dat[' . $key . ']" value="True" checked></td><td>' . htmlspecialchars($field->description) . '</td></tr>',
                    default => '<tr><td>' . htmlspecialchars($key) . '</td><td><input type="text" name="dat[' . $key . ']"' . $default . '></td><td>' . htmlspecialchars($field->description) . '</td></tr>',
                };
                echo $inputType;
            }
            echo '</table>';
        }

        echo '<br><input type="submit" value="Submit"></form>';
        break;

    case 'post':
        $method = filter_input(INPUT_POST, 'method', FILTER_SANITIZE_STRING);
        if (!isset($remoteData->$method)) {
            die("Ungültige Methode!");
        }

        $parameters = filter_input_array(INPUT_POST, FILTER_SANITIZE_SPECIAL_CHARS);
        if (!empty($_FILES['dat']['name']['filename'])) {
            require_once('config.php');
            $parameters['filename'] = $file_dest_dir . basename($_FILES['dat']['name']['filename']);
            move_uploaded_file($_FILES['dat']['tmp_name']['filename'], $parameters['filename']);
        }

        echo "<h2>Remote Admin: " . htmlspecialchars($method) . "</h2>";
        echo "<h3>Results of the call to the simulator.</h3>";
        $result = $remoteAdmin->SendCommand($method, $parameters);

        echo "<table border=\"1\" cellpadding=\"5\"><th>Parameter</th><th>Value</th>";
        foreach ($result as $key => $res) {
            if ($key === 'lastlogin') {
                $res = date('Y-m-d H:i:s', (int)$res);
            }
            echo "<tr><td>" . htmlspecialchars($key) . "</td><td>" . htmlspecialchars($res) . "</td></tr>";
        }
        echo "</table>";

        echo "<h3>Expected Results:</h3>";
        echo htmlspecialchars($remoteData->$method->ReturnedParameters);
        break;
}

?>

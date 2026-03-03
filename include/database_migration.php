<?php
// Include the database configuration
include 'include/config-file.php';

// SQL to add the new column
$sql = "ALTER TABLE `sm_route_lists` ADD `id_number` INT(225) NOT NULL AUTO_INCREMENT FIRST, ADD PRIMARY KEY (`id_number`)";

// Execute the query
if (mysqli_query($connection_server, $sql)) {
    echo "Table sm_route_lists altered successfully.";
} else {
    echo "Error altering table: " . mysqli_error($connection_server);
}

// Close the connection
mysqli_close($connection_server);
?>

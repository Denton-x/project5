<?php
// Option 1: store latest tag in a text file (simple)
$latest_tag_file = "latest_tag.txt";

if ($_SERVER['REQUEST_METHOD'] == "POST") {
    $data = json_decode(file_get_contents("php://input"), true);
    if(isset($data['tagid'])){
        file_put_contents($latest_tag_file, $data['tagid']);
        echo json_encode(["status"=>"success"]);
    } else {
        echo json_encode(["status"=>"error"]);
    }
    exit;
}

// GET request - return latest tag
if(file_exists($latest_tag_file)){
    $tagid = trim(file_get_contents($latest_tag_file));
    echo json_encode(["tagid"=>$tagid]);
} else {
    echo json_encode(["tagid"=>""]);
}
?>
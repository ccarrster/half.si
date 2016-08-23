<?php
// You need to add server side validation and better error handling here
include_once('./lib/QrReader.php');
$data = array();
$fileName = '';
if(isset($_FILES))
{  
    $error = false;
    $files = array();

    $uploaddir = './uploads/';
    foreach($_FILES as $file)
    {
        if(move_uploaded_file($file['tmp_name'], $uploaddir .basename($file['name'])))
        {
            $files[] = $uploaddir .$file['name'];
            $fileName = $uploaddir .$file['name'];
        }
        else
        {
            $error = true;
        }
    }
    $data = ($error) ? array('error' => 'There was an error uploading your files') : array('files' => $files);
}
else
{
    $data = array('success' => 'Form was submitted', 'formData' => $_POST);
}

$code = exec('zbarimg '.$fileName);
$data['text'] = explode(':', $code)[1];
echo json_encode($data);
?>
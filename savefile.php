<?php
// You need to add server side validation and better error handling here

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

$data['text'] = exec('zbarimg '.$fileName);
echo json_encode($data);
?>
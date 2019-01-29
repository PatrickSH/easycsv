<?php

function set_headers($name)
{
  header('Pragma: public');
  header('Expires: 0');
  header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
  header('Content-Description: File Transfer');
  header('Content-Type: text/csv');
  header(sprintf( 'Content-Disposition: attachment; filename=%s', $name ) );
  header('Content-Transfer-Encoding: binary');
}

function upload_to_ftp($settings,$file,$local_name){

  $settings = $settings;

  $con = ftp_connect($settings['host'],$settings['port']);
  ftp_pasv($con, $settings['passive']);
  // login with username and password
  $login_result = @ftp_login($con, $settings['username'], $settings['password']);

  if(@ftp_nb_put($con, $settings['path']."/".$local_name, $file, FTP_BINARY)){
    echo "Done!";
    exit;
  }

  die;
}

function settings($settings){
  $standard = [
    'name' => 'data-export'.date('y-m-d'),
    'delimeter' => ";",
    'file' => "php://output",
    'ftp' => [
      'port' => 21,
      'passive' => true,
      'host' => "",
      'username' => "",
      'password' => "",
      'path' => ""
    ]
  ];

  return array_replace($standard, $settings);
}

/**
 *
 * @param  array  $data Array of arrays with data.
 * @param  array  $data Array of settings.
 * @return [type]       [description]
 */
function to_csv(array $data = [], array $settings = [])
{
  $settings = settings($settings);

  if($settings['file'] == "php://output") set_headers($settings['name']);

  $output = fopen($settings['file'], "w");

  if(isset($data['head'])) fputcsv($output, $data['head'],$settings['delimeter']);

  if(isset($data['data'])){
    foreach($data['data'] as $csvdata){
      fputcsv($output, $csvdata,";");
    }
  }

  if(isset($data['footer'])) fputcsv($output, $data['footer'],$settings['delimeter']);

  fclose($output);
  if($settings['ftp']['host'] != ""){
    upload_to_ftp($settings['ftp'],$settings['file'],$settings['name']);
  }
  die;
}

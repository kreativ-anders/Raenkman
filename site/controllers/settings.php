<?php

return function ($kirby) {

  $vid = null;
  $voter = null;
  $voters = null;
  $options = null;
  $session = $kirby->session();
  $arr = array();

  $u = Db::min('user', 'ID', 'Identifier="'. Cookie::get('u') . '"');

  $voters = Db::select('voter', '*', ['User' => $u]);
  $options = Db::select('position', '*', ['User' => $u]);

  if ($kirby->request()->is('POST') && get('vid')) { // DELETE
    $vid = get('vid');
    
    $bool = Db::delete('voter', ['Identifier' => $vid]);
    //dump($bool);
  } 
  elseif ($kirby->request()->is('POST') && get('voter')) { //INSERT
    $voter = get('voter');

    $u = $session->get('u');

    $id = Db::insert('voter', [
      'User'          => $u,
      'Description'  => $voter,
      'Identifier'    => md5($voter)
    ]);

  }
  elseif (!Cookie::exists('u')) {
    return go('/');
  }
  else {

    $mysqli = new mysqli("localhost", "u204246837_rankman_user", "123456", "u204246837_rankman_db");

    $session = $kirby->session();
    $u = Db::min('user', 'ID', 'Identifier="'. Cookie::get('u') . '"');
    $v = Db::min('voter', 'ID', 'Identifier="'. Cookie::get('v') . '"');
    $session->set('u', $u); 
    $session->set('v', $v); 

    //$stmt = $mysqli->prepare("SELECT POSITION.ID, POSITION.Description FROM POSITION WHERE position.User = ? AND position.Owner IN (SELECT voter.ID FROM voter WHERE voter.Identifier != ?)");
    
    /*$u = $session->get('u');  
    $v = Cookie::get('v', null); 
    $stmt->bind_param("ss", $u, $v);*/

    //$stmt->execute();

    /* bind result variables */
    //$stmt->bind_result($IDs, $Descriptions);

    /*$arr = array();
    while ($stmt->fetch()) {
      $obj = array( 'ID' => $IDs,'Description' => $Descriptions);
      array_push($arr, $obj);
    }*/

    /*$arr = json_encode($arr);
    Cookie::set('p', $arr);
    $ranking = json_decode($arr);*/

    //$stmt->close();
    //$mysqli->close();
    // --------------------
mysqli_report(MYSQLI_REPORT_ALL ^ MYSQLI_REPORT_INDEX);
$stmt =  $mysqli->stmt_init();
  if ($stmt->prepare("SELECT position.ID, position.Description FROM position WHERE position.User = ? AND position.Owner IN (SELECT voter.ID FROM voter WHERE voter.Identifier != ?)")) {

    /* bind parameters for markers */
    $u = $session->get('u');  
    $v = Cookie::get('v', null); 
    $stmt->bind_param("ss", $u, $v);

    /* execute query */
    $stmt->execute();

    /* bind result variables */
    $stmt->bind_result($IDs, $Descriptions);

    /* fetch value */
    $stmt->fetch();

    while ($stmt->fetch()) {
      $obj = array('ID' => $IDs, 'Description' => $Descriptions);
      array_push($arr, (array) $obj);
    }
  
  
    $arr = json_encode($arr);
    Cookie::set('p', $arr);
    $ranking = json_decode($arr);

    /* close statement */
    $stmt->close();
}
//--------------------------------
$mysqli->close();
  }

  // pass $articles and $pagination to the template
  return [
    'voters' => $voters,
    'options' => $options
  ];

};
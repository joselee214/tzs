<?php
require_once __DIR__ . '/j7core_index_common.php';
require_once __DIR__ . '/createAction.php';
require_once __DIR__ . '/daomapAction.php';
require_once __DIR__ . '/indexAction.php';
require_once __DIR__ . '/basicmapAction.php';

class j7core_index_Action extends j7core_index_common
{

  public function test()
  {
    echo "ok test\n";
  }

  public function utf8mb4()
  {
    $db = $this->getDb();

    $r = $db->query('show tables');
    foreach($r as $row)
    {

//      $sql = 'ALTER TABLE `'.$row['Tables_in_nw'].'` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;';
//      $db->query($sql);

      $table = current($row);

      $sql1 = 'SHOW FULL COLUMNS FROM `'.$table.'`';
      $r1 = $db->query($sql1);
      foreach($r1 as $row1)
      {
        if( $row1['Collation']=='utf8_general_ci' )
        {
          $sql2 = 'ALTER TABLE `'.$table.'` CHANGE `'.$row1['Field'].'` `'.$row1['Field'].'` '.$row1['Type'].' CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci; ';

          echo $sql2 . PHP_EOL;
          $db->query($sql2);
        }
      }
    }
    echo "ok\n";
  }

}
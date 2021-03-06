<?php
/**
 * Display the syntenic block
 */

if ($block_info) {
  // define json data
  $json_data = '';
  $related = array();


  // display basic info of block 
  $rows = array();
  $headers = array();

  // show block ID
  $block_id = l($block_id, "synview/block/" . $block_id, array('html' => TRUE) );
  $rows[] = array(
    array(
      'data' => 'Block ID',
      'header' => TRUE,
      'width' => '20%',
    ),
    $block_id
  );

  // show organism A
  $orgA = $b1->organism_id->common_name;
  if (property_exists($b1->organism_id, 'nid')) {
    $orgA = l($orgA, "node/" . $b1->organism_id->nid, array('html' => TRUE));
  }
  if (function_exists('chado_get_record_entity_by_table')) {
      $entity_id = chado_get_record_entity_by_table ('organism', $b1->organism_id->organism_id);
      if ($entity_id) {
          $olink1 = "/bio_data/$entity_id";
          $orgA = l($orgA, $olink1, array('html' => TRUE));
      }
  }

  $rows[] = array(
    array(
      'data' => 'Organism A',
      'header' => TRUE,
      'width' => '20%',
    ),
    $orgA
  );

  $locA = $b1->featureloc->feature_id->srcfeature_id->uniquename . " : " .
    $b1->featureloc->feature_id->fmin . " - ". 
    $b1->featureloc->feature_id->fmax;
  $rows[] = array(
    array(
      'data' => 'Location A',
      'header' => TRUE,
      'width' => '20%',
    ),
    $locA
  );
  // show organism B
  $orgB = $b2->organism_id->common_name;
  if (property_exists($b2->organism_id, 'nid')) {
    $orgB = l($orgB, "node/" . $b2->organism_id->nid, array('html' => TRUE));
  }
  if (function_exists('chado_get_record_entity_by_table')) {
      $entity_id = chado_get_record_entity_by_table ('organism', $b2->organism_id->organism_id);
      if ($entity_id) {
          $olink2 = "/bio_data/$entity_id";
          $orgB = l($orgB, $olink2, array('html' => TRUE));
      }
  }
  
  $rows[] = array(
    array(
      'data' => 'Organism B',
      'header' => TRUE,
      'width' => '20%',
    ),
    $orgB
  );
  $locB = $b2->featureloc->feature_id->srcfeature_id->uniquename . " : " .
    $b2->featureloc->feature_id->fmin . " - ".
    $b2->featureloc->feature_id->fmax;
  $rows[] = array(
    array(
      'data' => 'Location B',
      'header' => TRUE,
      'width' => '20%',
    ),
    $locB
  );

  $table = array(
    'header' => $headers,
    'rows' => $rows,
    'attributes' => array(
      'id' => 'tripal_synview-display-block-basic',
      'class' => 'tripal-data-table table'
    ),
    'sticky' => FALSE,
    'caption' => '',
    'colgroups' => array(),
    'empty' => '',
  );

  $table_html = theme('table', $table);
  print '<br><div class="row" style="margin:0px">' . $table_html . '</div> </div>';

  // display gene pairs in block
  $synteny_gene_pairs = array();
  $rect1 = array(); $rect2 = array();
  $rows = array();
  $headers = array('Gene A' , 'Gene B', 'e-value');

  $id1_num = 0;
  $id2_num = 0;

  foreach ($block_info as $m) {
    $color = '';
    $id1 = $m[0];
    $id2 = $m[1];
    $id1_table = $id1;
    $id2_table = $id2;

    if (isset($gene_position[$id1]) && $gene_position[$id1]) {
		//$rect1[$id1] = array('min'=>$gene_position[$id1][0], 'max'=> $gene_position[$id1][1]);
      $rect1[] = array($gene_position[$id1]['name'], $gene_position[$id1]['fmin'], $gene_position[$id1]['fmax']);
        $id1_num++;
    }
    if (isset($gene_position[$id2]) && $gene_position[$id2]) {
		//$rect2[$id2] = array('min'=>$gene_position[$id2][0], 'max'=> $gene_position[$id2][1]);
      $rect2[] = array($gene_position[$id2]['name'], $gene_position[$id2]['fmin'], $gene_position[$id2]['fmax']);
        $id2_num++;
    }

    if (isset($_SESSION['tripal_synview_search']['highlight']) && ($_SESSION['tripal_synview_search']['highlight'] == $id1 or 
        $_SESSION['tripal_synview_search']['highlight'] == $id2)) {
      $color = '#ffa5a5';
    }

    // quick, just 500ms to display, must set /feature/gene/ to dispaly gene feature, not universal
    if ($id1 != 'NA') {
      $f1 = tripal_syncview_get_feature($id1, array('mRNA', 'gene', 'CDS'), $b1->organism_id->organism_id);
      $nid1 = chado_get_nid_from_id ('feature', $f1->feature_id);
      $link1 = "/node/" . $nid1;
      if (function_exists('chado_get_record_entity_by_table') && $f1->feature_id) {
          $entity_id = chado_get_record_entity_by_table ('feature', $f1->feature_id);
          if ($entity_id) {
              $link1 = "/bio_data/$entity_id";
          }
      }
      $id1_table = l($f1->name, $link1, array('html' => TRUE));
    }
    if ($id2 != 'NA') {
      $f2 = tripal_syncview_get_feature($id2, array('mRNA', 'gene', 'CDS'), $b2->organism_id->organism_id);
      $nid2 = chado_get_nid_from_id ('feature', $f2->feature_id);
      $link2 = "/node/" . $nid2;
      if (function_exists('chado_get_record_entity_by_table') && $f2->feature_id) {
          $entity_id = chado_get_record_entity_by_table ('feature', $f2->feature_id);
          if ($entity_id) {
              $link2 = "/bio_data/$entity_id";
          }
      }
      $id2_table = l($f2->name, $link2, array('html' => TRUE));
    }

    $rows[] = array(
      array('data'=> $id1_table, 'width' => '30%', 'bgcolor' => $color),
      array('data'=> $id2_table, 'width' => '30%', 'bgcolor' => $color),
      array('data'=> $m[2], 'width' => '15%', 'bgcolor' => $color),
    );

    // save synteny gene paris to array for js
    if ($id1 != 'NA' and $id2 != 'NA') {
      //$synteny_gene_pairs[] = array('A' => $id1, 'B' => $id2);
	  $synteny_gene_pairs[] = array($id1_num, $id2_num);
    }
  }

  $table = array(
    'header' => $headers,
    'rows' => $rows,
    'attributes' => array(
      'id' => 'tripal_synview-display-block-gene',
      'class' => 'tripal-data-table table'
    ),
    'sticky' => FALSE,
    'caption' => '',
    'colgroups' => array(),
    'empty' => '',
  );

  $table_html = theme('table', $table);

  // === insert block data (json format) ===
  $genloc_array = array(
    'A' => array(
      //$b1->featureloc->feature_id->srcfeature_id->uniquename
      'name' => $b1->featureloc->feature_id->srcfeature_id->uniquename,
      'min'  => $b1->featureloc->feature_id->fmin,
      'max'  => $b1->featureloc->feature_id->fmax,
      'rect' => $rect1
    ),
    'B' => array(
      'name' => $b2->featureloc->feature_id->srcfeature_id->uniquename,
      'min'  => $b2->featureloc->feature_id->fmin,
      'max'  => $b2->featureloc->feature_id->fmax,
      'rect' => $rect2
    ),
  );
  $genloc_json = json_encode($genloc_array);
  $synteny_gene_pairs_json = json_encode($synteny_gene_pairs);

  $json_data = "var data = $genloc_json; var relate = $synteny_gene_pairs_json;";
  drupal_add_js($json_data, array('type'=>'inline', 'scope'=>'footer','weight' => 50));
 
  print '
<div class="row"> 
	<div class="col-md-7"> <svg id="svg" width="1000" height="800"></svg> </div>
	<div class="col-md-5" style="margin-top:30px;">' . $table_html . '</div> 
</div>';
}

?>


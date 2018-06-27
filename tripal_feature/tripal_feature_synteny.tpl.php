<?php
/* Typically in a Tripal template, the data needed is retrieved using a call to
 * chado_expand_var function.  For example, to retrieve all
 * of the feature relationships for this node, the following function call would
 * be made:
 *
 *   $feature = chado_expand_var($feature,'table','feature_relationship');
 *
 * However, this function call can be extremely slow when there are numerous
 * relationships. This is because the chado_expand_var function is recursive
 * and expands all data following the foreign key relationships tree.
 * Therefore, to speed retrieval of data, a special variable is provided to
 * this template:
 *
 *   $feature->all_relationships;
 *
 * This variable is an array with two sub arrays with the keys 'object' and
 * 'subject'.  The array with key 'object' contains relationships where the
 * feature is the object, and the array with the key 'subject' contains
 * relationships where the feature is the subject
 */
$feature = $variables['node']->feature;
$all_relationships = $feature->all_relationships;

// the paralogs and orthlogs are exist on both object and subject. No difference
// so put the paralog and orthlog to array
$object_rels = $all_relationships['object'];
$subject_rels = $all_relationships['subject'];

$synblock_ids = array();
$paralogs_sbj = array();
$paralogs_obj = array();
$orthlogs_sbj = array();
$orthlogs_obj = array();

if (count($object_rels) > 0 or count($subject_rels) > 0) {

  // first add in the subject relationships.
  foreach ($subject_rels as $rel_type => $rels){

    if ($rel_type == 'member of') {
      foreach($rels['syntenic_region'] AS $synblock) {
        $synblock_ids[] = $synblock->record->object_id->feature_id;
      }
    }
    
    if ($rel_type == 'paralogous to' || $rel_type == 'orthologous to') {
    } else {
      continue;
    }

    foreach ($rels as $obj_type => $objects){
      if ($obj_type == 'gene' || $obj_type == 'mRNA') {
        if ($rel_type == 'paralogous to') {
          $paralogs_obj = $objects;
        }
        if ($rel_type == 'orthologous to') {
          $orthlogs_obj = $objects;  
        }
      }
    }
  }

  // second add in the object relationships.
  foreach ($object_rels as $rel_type => $rels){

    if ($rel_type == 'paralogous to' || $rel_type == 'orthologous to') {
    } else {
      continue;
    }

    foreach ($rels as $subject_type => $subjects){
      $verb = 'are';
      // only consider the gene feature as paralogous or orthlogous
      if ($subject_type == 'gene' || $subject_type == 'mRNA') { 
        if ($rel_type == 'paralogous to') {
          $paralogs_sbj = $subjects;
        }
        if ($rel_type == 'orthologous to') {
          $orthlogs_sbj = $subjects; 
        }
      }
    }
  }
}

/**
 * Display paralogs and orthlogs for gene feature
 */ 
if (count($paralogs_sbj) > 0 || count($paralogs_obj) > 0) { ?>
  <div class="tripal_feature-data-block-desc tripal-data-block-desc"></div>
    <p>The following gene(s) are <b>paralogous to</b> this gene:</p><?php
  $rows = array();
  $headers = array('Feature' , 'Paralogous', 'Organism', 'Block');

  foreach ($paralogs_obj as $paralog) {
    $gene_name = $paralog->record->subject_id->name;
    if (isset($paralog->record->subject_id->nid)) {
        $gene_name = l($gene_name, "node/" . $paralog->record->subject_id->nid, array('attributes' => array('target' => "_blank")));
    }
    $para_name = $paralog->record->object_id->name;
    if (property_exists($paralog->record, 'nid')) {
      $para_name = l($para_name, "node/" . $paralog->record->nid, array('attributes' => array('target' => "_blank")));
    }

    $para_org  = $paralog->record->object_id->organism_id;
    $para_org_name = $paralog->record->object_id->organism_id->genus . ' ' . $paralog->record->object_id->organism_id->species;
    if (property_exists($para_org, 'nid')) {
      $para_org_name = l($para_org_name, "node/" . $para_org->nid, array('html' => TRUE));
    }

    $block_id = get_block_id($paralog->record->object_id->feature_id, $synblock_ids);

    $rows[] = array(
      array('data'=> $gene_name, 'width' => '20%'),
      array('data'=> $para_name, 'width' => '20%'),
      array('data'=> $para_org_name, 'width' => '20%'),
      array('data'=> $block_id, 'width' => '25%'),
    );
  }

  foreach ($paralogs_sbj as $paralog) {
    $gene_name = $paralog->record->object_id->name;
    if (isset($paralog->record->object_id->nid)) {
        $gene_name = l($gene_name, "node/" . $paralog->record->object_id->nid, array('attributes' => array('target' => "_blank")));
    }
    $para_name = $paralog->record->subject_id->name;
    if (property_exists($paralog->record, 'nid')) {
      $para_name = l($para_name, "node/" . $paralog->record->nid, array('attributes' => array('target' => "_blank")));
    }

    $para_org  = $paralog->record->subject_id->organism_id;
    $para_org_name = $paralog->record->subject_id->organism_id->genus . ' ' . $paralog->record->subject_id->organism_id->species;
    if (property_exists($para_org, 'nid')) {
      $para_org_name = l($para_org_name, "node/" . $para_org->nid, array('html' => TRUE));
    }

    $block_id = get_block_id($paralog->record->subject_id->feature_id, $synblock_ids);

    $rows[] = array(
      array('data'=> $gene_name, 'width' => '20%'),
      array('data'=> $para_name, 'width' => '20%'),
      array('data'=> $para_org_name, 'width' => '20%'),
      array('data'=> $block_id, 'width' => '25%'),
    );
  }

  $table = array(
    'header' => $headers,
    'rows' => $rows,
    'attributes' => array(
      'id' => 'tripal_feature-table-paralogous',
      'class' => 'tripal-data-table table'
    ),
    'sticky' => FALSE,
    'caption' => '',
    'colgroups' => array(),
    'empty' => '',
  );

  print theme_table($table);
}

if (count($orthlogs_sbj) > 0 || count($orthlogs_obj) > 0) {

  ?><p>The following gene(s) are <b>orthologous to</b> this gene:</p><?php
  $rows = array();
  $headers = array('Feature', 'Orthologous', 'Organism', 'Block');

  foreach ($orthlogs_obj as $orthlog) {
    $gene_name = $orthlog->record->subject_id->name;
    if (isset($orthlog->record->subject_id->nid)) {
        $gene_name = l($gene_name, "node/" . $orthlog->record->subject_id->nid, array('attributes' => array('target' => "_blank")));
    }
    $orth_name = $orthlog->record->object_id->name;
    if (property_exists($orthlog->record, 'nid')) {
      $orth_name = l($orth_name, "node/" . $orthlog->record->nid, array('attributes' => array('target' => "_blank")));
    }

    $orth_org  = $orthlog->record->object_id->organism_id;
    $orth_org_name = $orthlog->record->object_id->organism_id->genus . ' ' . $orthlog->record->object_id->organism_id->species;
    if (property_exists($orth_org, 'nid')) {
      $orth_org_name = l($orth_org_name, "node/" . $orth_org->nid, array('html' => TRUE));
    }

    $block_id = get_block_id($orthlog->record->object_id->feature_id, $synblock_ids);

    $rows[] = array(
      array('data'=> $gene_name, 'width' => '20%'),
      array('data'=> $orth_name, 'width' => '20%'),
      array('data'=> $orth_org_name, 'width' => '20%'),
      array('data'=> $block_id, 'width' => '25%'),
    );
  }

  foreach ($orthlogs_sbj as $orthlog) {
    $gene_name = $orthlog->record->object_id->name;
    if (isset($orthlog->record->object_id->nid)) {
        $gene_name = l($gene_name, "node/" . $orthlog->record->object_id->nid, array('attributes' => array('target' => "_blank")));
    }
    $orth_name = $orthlog->record->subject_id->name;
    if (isset($orthlog->record->subject_id->nid)) {
        $orth_name = l($orth_name, "node/" . $orthlog->record->subject_id->nid, array('attributes' => array('target' => "_blank")));
    }

    $orth_org  = $orthlog->record->subject_id->organism_id;
    $orth_org_name = $orthlog->record->subject_id->organism_id->genus . ' ' . $orthlog->record->subject_id->organism_id->species;
    if (property_exists($orth_org, 'nid')) {
      $orth_org_name = l($orth_org_name, "node/" . $orth_org->nid, array('html' => TRUE));
    }
 
    $block_id = get_block_id($orthlog->record->subject_id->feature_id, $synblock_ids);
 
    $rows[] = array(
      array('data'=> $gene_name, 'width' => '20%'),
      array('data'=> $orth_name, 'width' => '20%'),
      array('data'=> $orth_org_name, 'width' => '20%'),
      array('data'=> $block_id, 'width' => '25%'),
    );
  }

  $table = array(
    'header' => $headers,
    'rows' => $rows,
    'attributes' => array(
      'id' => 'tripal_feature-table-orthologous',
      'class' => 'tripal-data-table table'
    ),
    'sticky' => FALSE,
    'caption' => '',
    'colgroups' => array(),
    'empty' => '',
  );

  print theme_table($table);
}

function get_block_id ($gid, $synblock_ids) {
  $block_heml = '';
  if (is_array($synblock_ids) && $gid) {
    $blk_sql = 
      "SELECT object_id 
       FROM {feature_relationship} 
       WHERE 
         type_id = 
           (SELECT cvterm_id FROM {cvterm} WHERE name = 'member_of' 
            AND cv_id = (SELECT cv_id FROM {cv} WHERE name = 'sequence'))
       AND 
         subject_id = :gid";
    $bids = chado_query($blk_sql, array(':gid' => $gid));
    while ($bid = $bids->fetchObject()) {
      $synblock_ids [] = $bid->object_id;
    }
    $block_id = db_query("SELECT blockid FROM {synblock} WHERE b2 IN (:blocks) AND b1 IN (:blocks)", array(':blocks' => $synblock_ids))->fetchField();
    $block_html = $block_id ? l($block_id, "synview/block/" . $block_id, array('attributes' => array('target' => "_blank"))) : 'NA';
  }
  return $block_html;
}


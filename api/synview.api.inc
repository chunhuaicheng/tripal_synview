<?php

/**
 * @file
 * Contains more generally applicable functions as well as some meant to help developers
 * Plug-in to the synview 
 */

/**
 * Get block info
 *
 * @param $block_id
 *
 * @return
 *   a stdclass of block_info
 */

/**
 * retrieve organism from database
 *
 * @return array of organism, key: organism_id value: common_name
 */

function get_reference ($genome_opts) {
  $result = chado_select_record('cvterm', array('cvterm_id'), array('name' => 'syntenic_region'));
  $type_id = $result[0]->cvterm_id;
  $sql = "SELECT R.feature_id, R.uniquename, R.seqlen FROM chado.feature R
    LEFT JOIN chado.featureloc FL ON R.feature_id = FL.srcfeature_id
    LEFT JOIN chado.feature F ON FL.feature_id = F.feature_id
    INNER JOIN chado.analysisfeature AF ON AF.feature_id = R.feature_id
    WHERE F.type_id = :type_id AND AF.analysis_id = :analysis_id
    ORDER BY R.feature_id ASC";
  foreach ($genome_opts as $genome_sel => $genome_name) {
    if (isset($_SESSION['tripal_synview_search']['REF'][$genome_sel]) and
        isset($_SESSION['tripal_synview_search']['RLEN'][$genome_sel]) and
        $_SESSION['tripal_synview_search']['REF'][$genome_sel] and
        $_SESSION['tripal_synview_search']['RLEN'][$genome_sel]) {
          continue;
        }
        $result = chado_query($sql, array(':type_id'=>$type_id, ':analysis_id'=>$genome_sel));
        $ref_name = array();
        $seqlen = array();
        foreach ($result as $r) {
          $ref_name[$r->feature_id] = $r->uniquename;
          $seqlen[$r->feature_id] = $r->seqlen;
        }
        // save the reference name array to session
        $_SESSION['tripal_synview_search']['REF'][$genome_sel] = $ref_name;
        $_SESSION['tripal_synview_search']['RLEN'][$genome_sel] = $seqlen;
  }
}

function get_block_from_chado($block_id) {
  $block_info = array();

  //if (isset($identifiers['nid'])) {
  //  $node = node_load($identifiers['nid']);
  //}
  //elseif (isset($identifiers['name'])) {
  //
  //  $nid = db_query('SELECT nid FROM {blastdb} WHERE name=:name', array(':name' => $identifiers['name']))->fetchField();
  //  $node = node_load($nid);
  //
  //} elseif (isset($identifiers['path'])) {
  //  $nid = db_query('SELECT nid FROM {blastdb} WHERE path LIKE :path', array(':path' => db_like($identifiers['path']) . '%'))->fetchField();
  //  $node = node_load($nid);
  //}

  return $block_info;
}

function get_block_by_location($org_id, $chr, $start, $end, $ref_org) {
  $block = array('test00001', 'test00002', 'test00003');
  return $block;
}

/**
 * Get all genomes
 */
function tripal_synview_get_genomes($parsed_only = FALSE) {
  $sql =
  "SELECT A.analysis_id, A.name FROM {analysis} A
    INNER JOIN {analysisprop} AP ON A.analysis_id = AP.analysis_id
    WHERE AP.value = 'whole_genome'
    AND AP.type_id = (SELECT cvterm_id FROM {cvterm} WHERE name = 'Analysis Type')
    ORDER BY A.name";
  $genomes = chado_query($sql);
  $genome_opts = array();
  foreach ($genomes as $genome) {
    if ($parsed_only) {
      $chk_sql = "SELECT nid FROM synfile WHERE genome1 = :analysis_id OR genome2 = :analysis_id";
      $genome_parsed = db_query($chk_sql, array(':analysis_id' => $genome->analysis_id))->fetchField();
      if (!$genome_parsed) {
        continue;
      }
    }
    $genome_opts[$genome->analysis_id] = $genome->name;
  }
  return $genome_opts;
}

/**
 * Get organism_id for the genome by checking its chromosomes and scaffolds
 */
function tripal_synview_get_organism_id ($analysis_id) {
  $sql =
  "SELECT organism_id
    FROM {feature} F
    INNER JOIN {analysisfeature} AF ON F.feature_id = AF.feature_id
    WHERE F.type_id = (SELECT cvterm_id FROM {cvterm} WHERE name = :type AND cv_id = (SELECT cv_id FROM {cv} WHERE name = 'sequence'))
    AND analysis_id = :analysis_id
    LIMIT 1";
  $org_id = chado_query($sql, array(':type' => 'chromosome', ':analysis_id' => $analysis_id))->fetchField();
  if (!$org_id) {
    $org_id = chado_query($sql, array(':type' => 'supercontig', ':analysis_id' => $analysis_id))->fetchField();
  }
  return $org_id;
}

/**
 * Get all chromosomes/scaffolds for a genome
 */
function tripal_synview_get_ref_sequences($analysis_id) {
  $seqs = array();
  if (is_numeric($analysis_id)) {
    $sql = "
      SELECT 
      DISTINCT 
        srcfeature_id AS feature_id, 
        (SELECT seqlen FROM {feature} WHERE feature_id = srcfeature_id) AS seqlen,
        (SELECT name FROM {feature} WHERE feature_id = srcfeature_id) AS name,
        (SELECT uniquename FROM {feature} WHERE feature_id = srcfeature_id) AS uniquename 
      FROM {feature} F 
      INNER JOIN {featureloc} FL ON F.feature_id = FL.feature_id
      INNER JOIN {analysisfeature} AF ON AF.feature_id = FL.srcfeature_id 
      WHERE type_id = (SELECT cvterm_id FROM {cvterm} WHERE name = 'syntenic_region') 
      AND organism_id = :organism_id
      AND analysis_id = :analysis_id
      ORDER BY name";
    $results = chado_query($sql, array(':organism_id' => tripal_synview_get_organism_id($analysis_id), ':analysis_id' => $analysis_id));
    while ($ref = $results->fetchObject()) {
      $seqs[$ref->feature_id] = $ref->name;
      $_SESSION['tripal_synview_search']['REF'][$analysis_id] [$ref->feature_id] = $ref->uniquename;
      $_SESSION['tripal_synview_search']['RLEN'][$analysis_id] [$ref->feature_id] = $ref->seqlen;
    }
  }
  return $seqs;
}

function tripal_syncview_get_feature ($uniquename, $types = array('mRNA', 'gene'), $organism_id = NULL) {
  $sql = 
  "SELECT feature_id, name FROM {feature} 
    WHERE uniquename = :uniquename 
    AND type_id IN 
      (SELECT cvterm_id FROM {cvterm} 
       WHERE name IN (:types)
       AND cv_id =
         (SELECT cv_id FROM {cv} WHERE name = 'sequence'))";
  if ($organism_id) {
    $sql .= " AND organism_id = :organism_id";
    return chado_query($sql, array(':uniquename' => $uniquename, ':types' => $types, ':organism_id' => $organism_id))->fetchObject();
  }
  else {
    return chado_query($sql, array(':uniquename' => $uniquename, ':types' => $types))->fetchObject();
  }
}
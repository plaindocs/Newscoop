<?php
/**
 * Campsite customized Smarty plugin
 * @package Campsite
 */


/**
 * Campsite list_subtopics block plugin
 *
 * Type:     block
 * Name:     list_subtopics
 * Purpose:  Provides a...
 *
 * @param string
 *     $p_params
 * @param string
 *     $p_smarty
 * @param string
 *     $p_content
 *
 * @return
 *
 */
function smarty_block_list_subtopics($p_params, $p_content, &$p_smarty, &$p_repeat)
{
    $p_smarty->smarty->loadPlugin('smarty_shared_escape_special_chars');
    $campContext = $p_smarty->getTemplateVars('gimme');

    if (!isset($p_content)) {
        $start = $campContext->next_list_start('SubtopicsList');
    	$subtopicsList = new SubtopicsList($start, $p_params);
    	if ($subtopicsList->isEmpty()) {
            $campContext->setCurrentList($subtopicsList, array());
            $campContext->resetCurrentList();
    		$p_repeat = false;
    	    return null;
    	}
    	$campContext->setCurrentList($subtopicsList, array('topic'));
    	$campContext->topic = $campContext->current_subtopics_list->current;
    	$p_repeat = true;
    } else {
        $campContext->current_subtopics_list->defaultIterator()->next();
        if (!is_null($campContext->current_subtopics_list->current)) {
            $campContext->topic = $campContext->current_subtopics_list->current;
            $p_repeat = true;
        } else {
            $campContext->resetCurrentList();
            $p_repeat = false;
        }
    }

    return $p_content;
}

?>

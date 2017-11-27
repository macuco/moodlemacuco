<?php
defined('MOODLE_INTERNAL') || die;

require_once($CFG->dirroot . '/grade/report/user/lib.php');
require_once($CFG->dirroot . '/mod/inea/inealib.php');

if (!defined('MIN_ACTIVATION')) {
    define('MIN_ACTIVATION', 80);
}

/**
 * INEA Clase para poder desactivar los examenes para darle secuencia a los examenes
 *
 * @package    mod_inea
 * @category   grade_report_user
 * @copyright  2017 macuco <juan.manuel.mp8@gmail.com> Juan Manuel Muñoz Pérez
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @since      Moodle 3.0
 */
class inea_grade_report_user extends grade_report_user {
    
    private $calificacion = array();
    private $avance = array();
    private $unidad = 0;
    
    
    public function __construct($courseid, $gpr, $context, $userid, $viewasuser = null) {
        parent::__construct($courseid, $gpr, $context, $userid);
    }
    
    function fill_table() {
        //print "<pre>";
        //print_r($this->gtree->top_element);
        $this->fill_table_recursive($this->gtree->top_element);
        //print_r($this->tabledata);
        //print "</pre>";
        return true;
    }
    
    private function fill_table_recursive(&$element) {
        global $DB, $CFG;
        
        
        $type = $element['type'];
        $depth = $element['depth'];
        $grade_object = $element['object'];
        $eid = $grade_object->id;
        $element['userid'] = $this->user->id;
        $fullname = $this->gtree->get_element_header($element, $this->puedeHacerExamen($element), true, true, true, true);
        $data = array();
        $gradeitemdata = array();
        $hidden = '';
        $excluded = '';
        $itemlevel = ($type == 'categoryitem' || $type == 'category' || $type == 'courseitem') ? $depth : ($depth + 1);
        $class = 'level' . $itemlevel . ' level' . ($itemlevel % 2 ? 'odd' : 'even');
        $classfeedback = '';
        
        // If this is a hidden grade category, hide it completely from the user
        if ($type == 'category' && $grade_object->is_hidden() && !$this->canviewhidden && (
            $this->showhiddenitems == GRADE_REPORT_USER_HIDE_HIDDEN ||
            ($this->showhiddenitems == GRADE_REPORT_USER_HIDE_UNTIL && !$grade_object->is_hiddenuntil()))) {
                return false;
            }
            
            if ($type == 'category') {
                $this->evenodd[$depth] = (($this->evenodd[$depth] + 1) % 2);
            }
            $alter = ($this->evenodd[$depth] == 0) ? 'even' : 'odd';
            
            /// Process those items that have scores associated
            if ($type == 'item' or $type == 'categoryitem' or $type == 'courseitem') {
                $header_row = "row_{$eid}_{$this->user->id}";
                $header_cat = "cat_{$grade_object->categoryid}_{$this->user->id}";
                
                if (! $grade_grade = grade_grade::fetch(array('itemid'=>$grade_object->id,'userid'=>$this->user->id))) {
                    $grade_grade = new grade_grade();
                    $grade_grade->userid = $this->user->id;
                    $grade_grade->itemid = $grade_object->id;
                }
                
                $grade_grade->load_grade_item();
                
                /// Hidden Items
                if ($grade_grade->grade_item->is_hidden()) {
                    $hidden = ' dimmed_text';
                }
                
                $hide = false;
                // If this is a hidden grade item, hide it completely from the user.
                if ($grade_grade->is_hidden() && !$this->canviewhidden && (
                    $this->showhiddenitems == GRADE_REPORT_USER_HIDE_HIDDEN ||
                    ($this->showhiddenitems == GRADE_REPORT_USER_HIDE_UNTIL && !$grade_grade->is_hiddenuntil()))) {
                        $hide = true;
                    } else if (!empty($grade_object->itemmodule) && !empty($grade_object->iteminstance)) {
                        // The grade object can be marked visible but still be hidden if
                        // the student cannot see the activity due to conditional access
                        // and it's set to be hidden entirely.
                        $instances = $this->modinfo->get_instances_of($grade_object->itemmodule);
                        if (!empty($instances[$grade_object->iteminstance])) {
                            $cm = $instances[$grade_object->iteminstance];
                            $gradeitemdata['cmid'] = $cm->id;
                            if (!$cm->uservisible) {
                                // If there is 'availableinfo' text then it is only greyed
                                // out and not entirely hidden.
                                if (!$cm->availableinfo) {
                                    $hide = true;
                                }
                            }
                        }
                    }
                    
                    // Actual Grade - We need to calculate this whether the row is hidden or not.
                    $gradeval = $grade_grade->finalgrade;
                    $hint = $grade_grade->get_aggregation_hint();
                    if (!$this->canviewhidden) {
                        /// Virtual Grade (may be calculated excluding hidden items etc).
                        $adjustedgrade = $this->blank_hidden_total_and_adjust_bounds($this->courseid,
                            $grade_grade->grade_item,
                            $gradeval);
                        
                        $gradeval = $adjustedgrade['grade'];
                        
                        // We temporarily adjust the view of this grade item - because the min and
                        // max are affected by the hidden values in the aggregation.
                        $grade_grade->grade_item->grademax = $adjustedgrade['grademax'];
                        $grade_grade->grade_item->grademin = $adjustedgrade['grademin'];
                        $hint['status'] = $adjustedgrade['aggregationstatus'];
                        $hint['weight'] = $adjustedgrade['aggregationweight'];
                    } else {
                        // The max and min for an aggregation may be different to the grade_item.
                        if (!is_null($gradeval)) {
                            $grade_grade->grade_item->grademax = $grade_grade->get_grade_max();
                            $grade_grade->grade_item->grademin = $grade_grade->get_grade_min();
                        }
                    }
                    
                    
                    if (!$hide) {
                        /// Excluded Item
                        /**
                         if ($grade_grade->is_excluded()) {
                         $fullname .= ' ['.get_string('excluded', 'grades').']';
                         $excluded = ' excluded';
                         }
                         **/
                        
                        /// Other class information
                        $class .= $hidden . $excluded;
                        if ($this->switch) { // alter style based on whether aggregation is first or last
                            $class .= ($type == 'categoryitem' or $type == 'courseitem') ? " ".$alter."d$depth baggt b2b" : " item b1b";
                        } else {
                            $class .= ($type == 'categoryitem' or $type == 'courseitem') ? " ".$alter."d$depth baggb" : " item b1b";
                        }
                        if ($type == 'categoryitem' or $type == 'courseitem') {
                            $header_cat = "cat_{$grade_object->iteminstance}_{$this->user->id}";
                        }
                        
                        /// Name
                        $data['itemname']['content'] = $fullname;
                        $data['itemname']['class'] = $class;
                        $data['itemname']['colspan'] = ($this->maxdepth - $depth);
                        $data['itemname']['celltype'] = 'th';
                        $data['itemname']['id'] = $header_row;
                        
                        // Basic grade item information.
                        $gradeitemdata['id'] = $grade_object->id;
                        $gradeitemdata['itemname'] = $grade_object->itemname;
                        $gradeitemdata['itemtype'] = $grade_object->itemtype;
                        $gradeitemdata['itemmodule'] = $grade_object->itemmodule;
                        $gradeitemdata['iteminstance'] = $grade_object->iteminstance;
                        $gradeitemdata['itemnumber'] = $grade_object->itemnumber;
                        $gradeitemdata['categoryid'] = $grade_object->categoryid;
                        $gradeitemdata['outcomeid'] = $grade_object->outcomeid;
                        $gradeitemdata['scaleid'] = $grade_object->outcomeid;
                        
                        if ($this->showfeedback) {
                            // Copy $class before appending itemcenter as feedback should not be centered
                            $classfeedback = $class;
                        }
                        $class .= " itemcenter ";
                        if ($this->showweight) {
                            $data['weight']['class'] = $class;
                            $data['weight']['content'] = '-';
                            $data['weight']['headers'] = "$header_cat $header_row weight";
                            // has a weight assigned, might be extra credit
                            
                            // This obliterates the weight because it provides a more informative description.
                            if (is_numeric($hint['weight'])) {
                                $data['weight']['content'] = format_float($hint['weight'] * 100.0, 2) . ' %';
                                $gradeitemdata['weightraw'] = $hint['weight'];
                                $gradeitemdata['weightformatted'] = $data['weight']['content'];
                            }
                            if ($hint['status'] != 'used' && $hint['status'] != 'unknown') {
                                $data['weight']['content'] .= '<br>' . get_string('aggregationhint' . $hint['status'], 'grades');
                                $gradeitemdata['status'] = $hint['status'];
                            }
                        }
                        
                        if ($this->showgrade) {
                            $gradeitemdata['graderaw'] = null;
                            $gradeitemdata['gradehiddenbydate'] = false;
                            $gradeitemdata['gradeneedsupdate'] = $grade_grade->grade_item->needsupdate;
                            $gradeitemdata['gradeishidden'] = $grade_grade->is_hidden();
                            $gradeitemdata['gradedatesubmitted'] = $grade_grade->get_datesubmitted();
                            $gradeitemdata['gradedategraded'] = $grade_grade->get_dategraded();
                            
                            if ($grade_grade->grade_item->needsupdate) {
                                $data['grade']['class'] = $class.' gradingerror';
                                $data['grade']['content'] = get_string('error');
                            } else if (!empty($CFG->grade_hiddenasdate) and $grade_grade->get_datesubmitted() and !$this->canviewhidden and $grade_grade->is_hidden()
                                and !$grade_grade->grade_item->is_category_item() and !$grade_grade->grade_item->is_course_item()) {
                                    // the problem here is that we do not have the time when grade value was modified, 'timemodified' is general modification date for grade_grades records
                                    $class .= ' datesubmitted';
                                    $data['grade']['class'] = $class;
                                    $data['grade']['content'] = get_string('submittedon', 'grades', userdate($grade_grade->get_datesubmitted(), get_string('strftimedatetimeshort')));
                                    $gradeitemdata['gradehiddenbydate'] = true;
                                } else if ($grade_grade->is_hidden()) {
                                    $data['grade']['class'] = $class.' dimmed_text';
                                    $data['grade']['content'] = '-';
                                    
                                    if ($this->canviewhidden) {
                                        $gradeitemdata['graderaw'] = $gradeval;
                                        $data['grade']['content'] = grade_format_gradevalue($gradeval,
                                            $grade_grade->grade_item,
                                            true);
                                    }
                                } else {
                                    $data['grade']['class'] = $class;
                                    $data['grade']['content'] = grade_format_gradevalue($gradeval,
                                        $grade_grade->grade_item,
                                        true);
                                    $gradeitemdata['graderaw'] = $gradeval;
                                }
                                $data['grade']['headers'] = "$header_cat $header_row grade";
                                $gradeitemdata['gradeformatted'] = $data['grade']['content'];
                        }
                        
                        // Range
                        if ($this->showrange) {
                            $data['range']['class'] = $class;
                            $data['range']['content'] = $grade_grade->grade_item->get_formatted_range(GRADE_DISPLAY_TYPE_REAL, $this->rangedecimals);
                            $data['range']['headers'] = "$header_cat $header_row range";
                            
                            $gradeitemdata['rangeformatted'] = $data['range']['content'];
                            $gradeitemdata['grademin'] = $grade_grade->grade_item->grademin;
                            $gradeitemdata['grademax'] = $grade_grade->grade_item->grademax;
                        }
                        
                        // Percentage
                        if ($this->showpercentage) {
                            if ($grade_grade->grade_item->needsupdate) {
                                $data['percentage']['class'] = $class.' gradingerror';
                                $data['percentage']['content'] = get_string('error');
                            } else if ($grade_grade->is_hidden()) {
                                $data['percentage']['class'] = $class.' dimmed_text';
                                $data['percentage']['content'] = '-';
                                if ($this->canviewhidden) {
                                    $data['percentage']['content'] = grade_format_gradevalue($gradeval, $grade_grade->grade_item, true, GRADE_DISPLAY_TYPE_PERCENTAGE);
                                }
                            } else {
                                $data['percentage']['class'] = $class;
                                $data['percentage']['content'] = grade_format_gradevalue($gradeval, $grade_grade->grade_item, true, GRADE_DISPLAY_TYPE_PERCENTAGE);
                            }
                            $data['percentage']['headers'] = "$header_cat $header_row percentage";
                            $gradeitemdata['percentageformatted'] = $data['percentage']['content'];
                        }
                        
                        // Lettergrade
                        if ($this->showlettergrade) {
                            if ($grade_grade->grade_item->needsupdate) {
                                $data['lettergrade']['class'] = $class.' gradingerror';
                                $data['lettergrade']['content'] = get_string('error');
                            } else if ($grade_grade->is_hidden()) {
                                $data['lettergrade']['class'] = $class.' dimmed_text';
                                if (!$this->canviewhidden) {
                                    $data['lettergrade']['content'] = '-';
                                } else {
                                    $data['lettergrade']['content'] = grade_format_gradevalue($gradeval, $grade_grade->grade_item, true, GRADE_DISPLAY_TYPE_LETTER);
                                }
                            } else {
                                $data['lettergrade']['class'] = $class;
                                $data['lettergrade']['content'] = grade_format_gradevalue($gradeval, $grade_grade->grade_item, true, GRADE_DISPLAY_TYPE_LETTER);
                            }
                            $data['lettergrade']['headers'] = "$header_cat $header_row lettergrade";
                            $gradeitemdata['lettergradeformatted'] = $data['lettergrade']['content'];
                        }
                        
                        // Rank
                        if ($this->showrank) {
                            $gradeitemdata['rank'] = 0;
                            if ($grade_grade->grade_item->needsupdate) {
                                $data['rank']['class'] = $class.' gradingerror';
                                $data['rank']['content'] = get_string('error');
                            } elseif ($grade_grade->is_hidden()) {
                                $data['rank']['class'] = $class.' dimmed_text';
                                $data['rank']['content'] = '-';
                            } else if (is_null($gradeval)) {
                                // no grade, no rank
                                $data['rank']['class'] = $class;
                                $data['rank']['content'] = '-';
                                
                            } else {
                                /// find the number of users with a higher grade
                                $sql = "SELECT COUNT(DISTINCT(userid))
                                  FROM {grade_grades}
                                 WHERE finalgrade > ?
                                       AND itemid = ?
                                       AND hidden = 0";
                                $rank = $DB->count_records_sql($sql, array($grade_grade->finalgrade, $grade_grade->grade_item->id)) + 1;
                                
                                $data['rank']['class'] = $class;
                                $numusers = $this->get_numusers(false);
                                $data['rank']['content'] = "$rank/$numusers"; // Total course users.
                                
                                $gradeitemdata['rank'] = $rank;
                                $gradeitemdata['numusers'] = $numusers;
                            }
                            $data['rank']['headers'] = "$header_cat $header_row rank";
                        }
                        
                        // Average
                        if ($this->showaverage) {
                            $gradeitemdata['averageformatted'] = '';
                            
                            $data['average']['class'] = $class;
                            if (!empty($this->gtree->items[$eid]->avg)) {
                                $data['average']['content'] = $this->gtree->items[$eid]->avg;
                                $gradeitemdata['averageformatted'] = $this->gtree->items[$eid]->avg;
                            } else {
                                $data['average']['content'] = '-';
                            }
                            $data['average']['headers'] = "$header_cat $header_row average";
                        }
                        
                        // Feedback
                        if ($this->showfeedback) {
                            $gradeitemdata['feedback'] = '';
                            $gradeitemdata['feedbackformat'] = $grade_grade->feedbackformat;
                            
                            if ($grade_grade->overridden > 0 AND ($type == 'categoryitem' OR $type == 'courseitem')) {
                                $data['feedback']['class'] = $classfeedback.' feedbacktext';
                                $data['feedback']['content'] = get_string('overridden', 'grades').': ' . format_text($grade_grade->feedback, $grade_grade->feedbackformat);
                                $gradeitemdata['feedback'] = $grade_grade->feedback;
                            } else if (empty($grade_grade->feedback) or (!$this->canviewhidden and $grade_grade->is_hidden())) {
                                $data['feedback']['class'] = $classfeedback.' feedbacktext';
                                $data['feedback']['content'] = '&nbsp;';
                            } else {
                                $data['feedback']['class'] = $classfeedback.' feedbacktext';
                                $data['feedback']['content'] = format_text($grade_grade->feedback, $grade_grade->feedbackformat);
                                $gradeitemdata['feedback'] = $grade_grade->feedback;
                            }
                            $data['feedback']['headers'] = "$header_cat $header_row feedback";
                        }
                        // Contribution to the course total column.
                        if ($this->showcontributiontocoursetotal) {
                            $data['contributiontocoursetotal']['class'] = $class;
                            $data['contributiontocoursetotal']['content'] = '-';
                            $data['contributiontocoursetotal']['headers'] = "$header_cat $header_row contributiontocoursetotal";
                            
                        }
                        $this->gradeitemsdata[] = $gradeitemdata;
                    }
                    // We collect the aggregation hints whether they are hidden or not.
                    if ($this->showcontributiontocoursetotal) {
                        $hint['grademax'] = $grade_grade->grade_item->grademax;
                        $hint['grademin'] = $grade_grade->grade_item->grademin;
                        $hint['grade'] = $gradeval;
                        $parent = $grade_object->load_parent_category();
                        if ($grade_object->is_category_item()) {
                            $parent = $parent->load_parent_category();
                        }
                        $hint['parent'] = $parent->load_grade_item()->id;
                        $this->aggregationhints[$grade_grade->itemid] = $hint;
                    }
            }
            
            /// Category
            if ($type == 'category') {
                $data['leader']['class'] = $class.' '.$alter."d$depth b1t b2b b1l";
                $data['leader']['rowspan'] = $element['rowspan'];
                
                if ($this->switch) { // alter style based on whether aggregation is first or last
                    $data['itemname']['class'] = $class.' '.$alter."d$depth b1b b1t";
                } else {
                    $data['itemname']['class'] = $class.' '.$alter."d$depth b2t";
                }
                $data['itemname']['colspan'] = ($this->maxdepth - $depth + count($this->tablecolumns) - 1);
                $data['itemname']['content'] = $fullname;
                $data['itemname']['celltype'] = 'th';
                $data['itemname']['id'] = "cat_{$grade_object->id}_{$this->user->id}";
            }
            
            /// Add this row to the overall system
            foreach ($data as $key => $celldata) {
                $data[$key]['class'] .= ' column-' . $key;
            }
            $this->tabledata[] = $data;
            
            /// Recursively iterate through all child elements
            if (isset($element['children'])) {
                foreach ($element['children'] as $key=>$child) {
                    $this->fill_table_recursive($element['children'][$key]);
                }
            }
            
            // Check we are showing this column, and we are looking at the root of the table.
            // This should be the very last thing this fill_table_recursive function does.
            if ($this->showcontributiontocoursetotal && ($type == 'category' && $depth == 1)) {
                // We should have collected all the hints by now - walk the tree again and build the contributions column.
                
                $this->fill_contributions_column($element);
            }
    }
    
    /**
     * Funcion que determina si puede o no hacer el examen
     * @param unknown $element
     * @return boolean
     */
    private function puedeHacerExamen($element){
        
        if ($element['type'] != 'item' and $element['type'] != 'categoryitem' and
            $element['type'] != 'courseitem') {
                return true;
        }
        
        $itemtype = $element['object']->itemtype;
        $itemmodule = $element['object']->itemmodule;
        $iteminstance = $element['object']->iteminstance;
        $itemnumber = $element['object']->itemnumber;
     
        // Links only for module items that have valid instance, module and are
        // called from grade_tree with valid modinfo
        if ($itemtype != 'mod' || !$iteminstance || $itemmodule != 'quiz' || !$this->modinfo) {
            return true;
        }
        
        // Get $cm efficiently and with visibility information using modinfo
        $instances = $this->modinfo->get_instances();
        if (empty($instances[$itemmodule][$iteminstance])) {
            return true;
        }
        $cm = $instances[$itemmodule][$iteminstance];
       
       if (! $grade_grade = grade_grade::fetch(array('itemid'=>$element['object']->id,'userid'=>$this->user->id))) {
           $grade_grade = new grade_grade();
           $grade_grade->userid = $this->user->id;
           $grade_grade->itemid = $grade_object->id;
       }
       
       $grade_grade->load_grade_item();
       //print_object("MAX: ".$grade_grade->get_grade_max());       
       //print_object("FINAL: ".$grade_grade->finalgrade);
       
       
       $calificacion = ($grade_grade->finalgrade*100)/$grade_grade->get_grade_max();
       
       
       $avance = obtener_avance_unidad($this->user->id, $this->modinfo->courseid, $this->unidad+1);
       
       $this->calificacion[$this->unidad] = $calificacion;
       $this->avance[$this->unidad] = $avance;
       
       $unidad = $this->unidad;
       $this->unidad++;//Para incrementar de unidad y obtener los datos de la siguiente unidad
       
       if($this->avance[$unidad] >= MIN_ACTIVATION){
           if($unidad==0){ //en la primer unidad solo es necesario el avance
                return true;
           }else if($this->calificacion[$unidad-1]>=MIN_ACTIVATION){ //Para la primer unidad solo verificamos que tenga el avance del 80%
                return true;
           }
       }
       
       return false;
    }
    
}

<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * Edwiser RemUI Course Renderer Class
 * @package    theme_remui
 * @copyright  (c) 2018 WisdmLabs (https://wisdmlabs.com/)
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace theme_remui\output\core;
defined('MOODLE_INTERNAL') || die();

use core_completion\progress;
use core_course_renderer;
use moodle_url;
use coursecat_helper;
use lang_string;
use core_course_category;
use context_system;
use html_writer;
use core_text;

global $CFG;

require_once($CFG->dirroot . '/course/renderer.php');

class course_renderer extends \core_course_renderer {

	private function getRandomImage($courses) {
		if (!empty($courses)) {
			$course = $courses[array_rand($courses)];
			$image = \theme_remui\utility::get_course_image($course, 1);
			return $image;
		}
		return '';
	}

	private function progressBarHTML($course) {
		global $USER;
		$div = '<div style="background-color: white !important; height: 1rem; box-shadow: 0px 4px 4px rgba(0,0,0,.16);"></div>';

		$percentage = progress::get_course_progress_percentage($course, $USER->id);

		if($percentage === 0) {
			$div = '<div class="progress progress-square mb-0">
									<div class="progress-bar bg-red-600-cc" style="height: 100%; width: 100%; background-color: #FF644C !important;" role="progressbar">
											<span>' . $percentage . '%' . '</span>
									</div>
							</div>';
		} elseif($percentage > 0) {
			$div = '<div class="progress progress-square mb-0">
									<div class="progress-bar bg-green-600-cc" style="width: ' . $percentage . '%; height: 100%;" role="progressbar">
											<span>' . round($percentage) . '%' . '</span>
									</div>
							</div>';
		}

		return $div;
	}

	private function convertDateToSpanish($timestamp) {
		setlocale(LC_TIME, 'es_ES', 'Spanish_Spain', 'Spanish');
		return strftime("%d de %B de %Y", $timestamp);
	}

	private function getChildCoursesHTML($coursecatArr) {
		$html = '';

		foreach($coursecatArr as $coursecat) {
			$courses = $coursecat->get_courses();

			$html .= '<div class="cc-subcategory-title">
								<div>'.$coursecat->name.'</div>
								<hr style="margin-top: 0; border-top: 1px solid #cdc4c4; !important;">
							</div>';

			if(empty($courses)) {
				$html .= '<div><div style="margin-top: 3%; text-align: center; font-size: large" class="cc-category-container">Aún no existen cursos para esta categoría</div></div>';
			} elseif (!empty($courses)) {
				$html .= '<div><div class="row ml-0">';

				$current = 0;
				$rowCount = 0;
				$countForColor = 0;

				foreach($courses as $c) {
					$countForColor++;

					$html .= '<div class="cc-course-container-min">
												<div class="cc-course-div-box-dinamic" data-categoryid="1" data-depth="1" data-showcourses="5" data-type="0">
														<div class="cc-courses-image-container" style="background: url('. \theme_remui\utility::get_course_image($c, 1) .'); background-size: cover;"></div>
														'. $this->progressBarHTML($c) .' 
														<div class="cc-courses-detail-container cc-height-200" style="background-color: white;">
															<div class="cc-courses-cat-name">'. 'Lanzamiento: ' . $this->convertDateToSpanish($c->startdate) .'</div>
															<div class="cc-courses-course-name">'. $c->fullname .'</div>
															<a class="cc-courses-button" type="button" href="'. new moodle_url("/course/view.php",array("id" => $c->id)). '">Acceder al curso</a>
														</div>
												</div>
											</div>';
					$rowCount++;
					$current++;
				}
				$html .= '</div></div>';
			}
		}
		return $html;
	}

	private function getCoursesHTML($coursecat) {
		$courses = $coursecat->get_courses();
		$html = '';

		$current = 0;
		$rowCount = 0;
		$countForColor = 0;

		foreach($courses as $c) {
			$countForColor++;

			$html .= '<div class="cc-course-container-min">
												<div class="cc-course-div-box-dinamic" data-categoryid="1" data-depth="1" data-showcourses="5" data-type="0">
														<div class="cc-courses-image-container" style="background: url('. \theme_remui\utility::get_course_image($c, 1) .'); background-size: cover;"></div>
														'. $this->progressBarHTML($c) .' 
														<div class="cc-courses-detail-container cc-height-200" style="background-color: white;">
															<div class="cc-courses-cat-name">'. 'Lanzamiento: ' . $this->convertDateToSpanish($c->startdate) .'</div>
															<div class="cc-courses-course-name">'. $c->fullname .'</div>
															<a class="cc-courses-button" type="button" href="'. new moodle_url("/course/view.php",array("id" => $c->id)). '">Acceder al curso</a>
														</div>
												</div>
											</div>';
			$rowCount++;
			$current++;
		}
		$html .= $this->getChildCoursesHTML($coursecat->get_children());

		if($html == '') {
			$html .= '<div style="margin: auto;"> Aún no existen cursos para esta categoría </div>';
		}

		return $html;
	}

	/**
	 * Outputs contents for frontpage as configured in $CFG->frontpage or $CFG->frontpageloggedin
	 *
	 * @return string
	 */
	public function frontpage() {
		global $CFG, $SITE;

		$output = '';

		if (isloggedin() and !isguestuser() and isset($CFG->frontpageloggedin)) {
			$frontpagelayout = $CFG->frontpageloggedin;
		} else {
			$frontpagelayout = $CFG->frontpage;
		}

		foreach (explode(',', $frontpagelayout) as $v) {
			switch ($v) {
				// Display the main part of the front page.
				case FRONTPAGENEWS:
					if ($SITE->newsitems) {
						// Print forums only when needed.
						require_once($CFG->dirroot .'/mod/forum/lib.php');
						if (($newsforum = forum_get_course_forum($SITE->id, 'news')) &&
							($forumcontents = $this->frontpage_news($newsforum))) {
							$newsforumcm = get_fast_modinfo($SITE)->instances['forum'][$newsforum->id];
							$output .= $this->frontpage_part('skipsitenews', 'site-news-forum',
								$newsforumcm->get_formatted_name(), $forumcontents);
						}
					}
					break;

				case FRONTPAGEENROLLEDCOURSELIST:
					$mycourseshtml = $this->frontpage_my_courses();
					if (!empty($mycourseshtml)) {
						$output .= $this->frontpage_part('skipmycourses', 'frontpage-course-list',
							get_string('mycourses'), $mycourseshtml);
					}
					break;

				case FRONTPAGEALLCOURSELIST:
					$availablecourseshtml = $this->frontpage_available_courses();
					$output .= $this->frontpage_part('skipavailablecourses', 'frontpage-available-course-list',
						get_string('availablecourses'), $availablecourseshtml);
					break;

				case FRONTPAGECATEGORYNAMES:
					$output .= $this->frontpage_part('skipcategories', 'frontpage-category-names',
						'Buscar cursos por categorías', $this->frontpage_categories_list(), 'cc-category-header pr-0 pl-0 pb-0');
					break;

				case FRONTPAGECATEGORYCOMBO:
					$output .= $this->frontpage_part('skipcourses', 'frontpage-category-combo',
						get_string('courses'), $this->frontpage_combo_list());
					break;

				case FRONTPAGECOURSESEARCH:
					$output .= $this->box($this->course_search_form('', 'short'), 'mdl-align');
					break;

			}
		}
		return $output;
	}

	/**
	 * Renders part of frontpage with a skip link (i.e. "My courses", "Site news", etc.)
	 *
	 * @param string $skipdivid
	 * @param string $contentsdivid
	 * @param string $header Header of the part
	 * @param string $contents Contents of the part
	 * @param string $customclass
	 * @return string
	 */
	protected function frontpage_part($skipdivid, $contentsdivid, $header, $contents, $customclass='') {
		if (strval($contents) === '') {
			return '';
		}
		$output = html_writer::link('#' . $skipdivid,
			get_string('skipa', 'access', core_text::strtolower(strip_tags($header))),
			array('class' => 'skip-block skip'));

		// Wrap frontpage part in div container.
		$output .= html_writer::start_tag('div', array('id' => $contentsdivid, 'class' => $customclass));
		$output .= $this->heading($header);

		$output .= $contents;

		// End frontpage part div container.
		$output .= html_writer::end_tag('div');
		return $output;
	}

	/**
	 * Returns HTML to display a tree of subcategories and courses in the given category
	 *
	 * @param coursecat_helper $chelper various display options
	 * @param core_course_category $coursecat top category (this category's name and description will NOT be added to the tree)
	 * @return string
	 */
	protected function coursecat_tree(coursecat_helper $chelper, $coursecat) {
		// Reset the category expanded flag for this course category tree first.
		$this->categoryexpandedonload = false;
		$categorycontent = $this->coursecat_category_content($chelper, $coursecat, 0);
		if (empty($categorycontent)) {
			return '';
		}

		// Start content generation
		$content = '';
		$attributes = $chelper->get_and_erase_attributes('course_category_tree clearfix');
		$content .= html_writer::start_tag('div', $attributes);

		if ($coursecat->get_children_count()) {
			$classes = array(
				'collapseexpand',
			);

			// Check if the category content contains subcategories with children's content loaded.
			if ($this->categoryexpandedonload) {
				$classes[] = 'collapse-all';
				$linkname = get_string('collapseall');
			} else {
				$linkname = get_string('expandall');
			}

			// Only show the collapse/expand if there are children to expand.
//			$content .= html_writer::start_tag('div', array('class' => 'collapsible-actions'));
//			$content .= html_writer::link('#', $linkname, array('class' => implode(' ', $classes)));
//			$content .= html_writer::end_tag('div');
			$this->page->requires->strings_for_js(array('collapseall', 'expandall'), 'moodle');
		}

		$content .= html_writer::tag('div', $categorycontent, array('class' => 'content'));

		$content .= html_writer::end_tag('div'); // .course_category_tree

		return $content;
	}

	/**
	 * Returns HTML to print tree of course categories (with number of courses) for the frontpage
	 *
	 * @return string
	 */
	public function frontpage_categories_list() {
		global $CFG;
		// TODO MDL-10965 improve.
		$tree = core_course_category::top();
		if (!$tree->get_children_count()) {
			return '';
		}
		$chelper = new coursecat_helper();
		$chelper->set_subcat_depth($CFG->maxcategorydepth)->
		set_show_courses(self::COURSECAT_SHOW_COURSES_COUNT)->
		set_categories_display_options(array(
			'limit' => $CFG->coursesperpage,
			'viewmoreurl' => new moodle_url('/course/index.php',
				array('browse' => 'categories', 'page' => 1))
		))->
		set_attributes(array('class' => 'frontpage-category-names'));
		return $this->coursecat_tree($chelper, $tree);
	}

	/**
	 *  Get ultimos cursos
	 */
	private function getUltimosCursos($all=false) {
		//Custom content (ultimos cursos)

		$allcourses = core_course_category::get(0)->get_courses(
			array('recursive' => true, 'coursecontacts' => true, 'sort' => array('startdate' => 1)));

		if($all) {
			$titulo = 'Todos nuestros cursos';
			$offset = count($allcourses);
			$allcourses = array_slice($allcourses, 0, $offset, true);
			$divClass = "cc-ultimos-cursos-container-all";
			$divClassList = "cc-all-cursos-list";
			$width = "cc-all-cursos-block";
			$extraClasses = 'cc-height-200';
		} else {
			$titulo = 'Nuestros últimos cursos';
			$offset = 3;
			$allcourses = array_slice($allcourses, -3, $offset, true);
			$allcourses = array_reverse($allcourses, true);
			$divClass = "cc-ultimos-cursos-container";
			$divClassList = "cc-ultimos-cursos-list";
			$width = "col-sm";
			$extraClasses = '';
		}

		$allcourses = array_slice($allcourses, 0, $offset);

		$content =
			'<div class="'.$divClass.'">
						<div>
							<h2 class="cc-big-header">'.$titulo.'</h2>
						</div>
						<div class="'.$divClassList.'">';

		$rowCount = 0;
		$countForColor = 0;

		foreach($allcourses as $key=>$courseElement) {
			$countForColor++;

			if($rowCount == 0 && $all) {
				$content .=	'<div class="row">';
			}

			$content .=	'<div class="'. $width .'">
										<div class="cc-courses-image-container" style="background: url('. \theme_remui\utility::get_course_image($courseElement, 1) .');"></div>
										'. $this->progressBarHTML($courseElement) .'
										<div class="cc-courses-detail-container '. $extraClasses .'">
											<div class="cc-courses-cat-name">'. 'Lanzamiento: ' . $this->convertDateToSpanish($courseElement->startdate) .'</div>
											<div class="cc-courses-course-name">'. $courseElement->fullname .'</div>
											<a class="cc-courses-button" type="button" href="'. new moodle_url("/course/view.php",array("id" => $courseElement->id)). '">Acceder al curso</a></div></div>';

			$rowCount++;

			if($rowCount%3 == 0 && $all) {
				$content .=	'</div><div class="row">';
			}
		}

		$content .= '</div>';

		if($all) {
			$content .=
				'<div class="cc-ultimos-cursos-button-div" style="padding-top: 5%;">
					<a class="cc-ultimos-cursos-button" type="button" href="' . new moodle_url("/") . '">Ver categorías</a>
				</div>';
		} else {
			$content .=
				'<div class="cc-ultimos-cursos-button-div">
					<a class="cc-ultimos-cursos-button" type="button" href="' . new moodle_url("/course") . '">Ver todos los cursos</a>
				</div>';
		}

		return $content;
	}

	/**
	 * Renders the list of subcategories in a category
	 *
	 * @param coursecat_helper $chelper various display options
	 * @param core_course_category $coursecat
	 * @param int $depth depth of the category in the current tree
	 * @return string
	 */
	protected function coursecat_subcategories(coursecat_helper $chelper, $coursecat, $depth) {
		global $CFG;
		$subcategories = array();
		if (!$chelper->get_categories_display_option('nodisplay')) {
			$subcategories = $coursecat->get_children($chelper->get_categories_display_options());
		}

		foreach($subcategories as $sub) {
			if($sub->parent != 0) {
				return '';
			}
		}

		$totalcount = $coursecat->get_children_count();
		if (!$totalcount) {
			// Note that we call core_course_category::get_children_count() AFTER core_course_category::get_children()
			// to avoid extra DB requests.
			// Categories count is cached during children categories retrieval.
			return '';
		}

		// prepare content of paging bar or more link if it is needed
		$paginationurl = $chelper->get_categories_display_option('paginationurl');
		$paginationallowall = $chelper->get_categories_display_option('paginationallowall');
		if ($totalcount > count($subcategories)) {
			if ($paginationurl) {
				// the option 'paginationurl was specified, display pagingbar
				$perpage = $chelper->get_categories_display_option('limit', $CFG->coursesperpage);
				$page = $chelper->get_categories_display_option('offset') / $perpage;
				$pagingbar = $this->paging_bar($totalcount, $page, $perpage,
					$paginationurl->out(false, array('perpage' => $perpage)));
				if ($paginationallowall) {
					$pagingbar .= html_writer::tag('div', html_writer::link($paginationurl->out(false, array('perpage' => 'all')),
						get_string('showall', '', $totalcount)), array('class' => 'paging paging-showall'));
				}
			} else if ($viewmoreurl = $chelper->get_categories_display_option('viewmoreurl')) {
				// the option 'viewmoreurl' was specified, display more link (if it is link to category view page, add category id)
				if ($viewmoreurl->compare(new moodle_url('/course/index.php'), URL_MATCH_BASE)) {
					$viewmoreurl->param('categoryid', $coursecat->id);
				}
				$viewmoretext = $chelper->get_categories_display_option('viewmoretext', new lang_string('viewmore'));
				$morelink = html_writer::tag('div', html_writer::link($viewmoreurl, $viewmoretext),
					array('class' => 'paging paging-morelink'));
			}
		} else if (($totalcount > $CFG->coursesperpage) && $paginationurl && $paginationallowall) {
			// there are more than one page of results and we are in 'view all' mode, suggest to go back to paginated view mode
			$pagingbar = html_writer::tag('div', html_writer::link($paginationurl->out(false, array('perpage' => $CFG->coursesperpage)),
				get_string('showperpage', '', $CFG->coursesperpage)), array('class' => 'paging paging-showperpage'));
		}

		// display list of subcategories
		$content = html_writer::start_tag('div', array('class' => 'subcategories'));

		if (!empty($pagingbar)) {
			$content .= $pagingbar;
		}

		$content .= '';

		$current = 0;
		$rowCount = 0;
		$countForColor = 0;
		$groupedNumber = 3;
		$boxColor = "blue";

		$content .= '<div class="moved-background"></div>';
		$content .= '<div class="cc-main-table-container pr-0 pl-0 w-100"><div style="width: 85%;margin: 0 auto;">';
		$coursesDivs = '';

		foreach ($subcategories as $key=>$subcategory) {

			if(!$subcategory->visible) {
				continue;
			}

			$countForColor++;

			if($countForColor === 1) {
				$boxColor = "blue";
			} else if($countForColor === 2) {
				$boxColor = "skyblue";
			} else if($countForColor === 3) {
				$boxColor = "green";
			}
			$content .= '<div category-id="'.$key.'" class="col-sm cc-category-container">';

			$content .= $this->coursecat_category($chelper, $subcategory, $depth + 1, $boxColor, $key);
			$content .= '</div>';
			$rowCount++;
			if($rowCount%$groupedNumber === 0) {
				$countForColor = 0;
			}
			$current++;
		}

		foreach ($subcategories as $key=>$subcategory) {
				$coursesDivs .= '<div class="cc-courses-div-detail row" category-id="'.$key.'">'.$this->getCoursesHTML($subcategory).'</div>';
		}

		$content .= '</div>';
		$content .= '<div class="cc-courses-div" style="position:relative; vertical-align:top; width:100%; background: #ececec; display:none;">';
		$content .= $coursesDivs;
		$content .= '</div></div>';

		$content .= '<input id="cc-total-cursos" type="hidden" value="'.$current.'">';

		$content .= $this->getUltimosCursos();

		if (!empty($pagingbar)) {
			$content .= $pagingbar;
		}
		if (!empty($morelink)) {
			$content .= $morelink;
		}

		$content .= html_writer::end_tag('div');
		return $content;
	}

	/**
	 * Returns HTML to display the subcategories and courses in the given category
	 *
	 * This method is re-used by AJAX to expand content of not loaded category
	 *
	 * @param coursecat_helper $chelper various display options
	 * @param core_course_category $coursecat
	 * @param int $depth depth of the category in the current tree
	 * @return string
	 */
	protected function coursecat_category_content(coursecat_helper $chelper, $coursecat, $depth) {
		$content = '';
		// Subcategories
		$content .= $this->coursecat_subcategories($chelper, $coursecat, $depth);

		// AUTO show courses: Courses will be shown expanded if this is not nested category,
		// and number of courses no bigger than $CFG->courseswithsummarieslimit.
		$showcoursesauto = $chelper->get_show_courses() == self::COURSECAT_SHOW_COURSES_AUTO;
		if ($showcoursesauto && $depth) {
			// this is definitely collapsed mode
			$chelper->set_show_courses(self::COURSECAT_SHOW_COURSES_COLLAPSED);
		}

		// Courses
		if ($chelper->get_show_courses() > core_course_renderer::COURSECAT_SHOW_COURSES_COUNT) {
			$courses = array();
			if (!$chelper->get_courses_display_option('nodisplay')) {
				$courses = $coursecat->get_courses($chelper->get_courses_display_options());
			}
			if ($viewmoreurl = $chelper->get_courses_display_option('viewmoreurl')) {
				// the option for 'View more' link was specified, display more link (if it is link to category view page, add category id)
				if ($viewmoreurl->compare(new moodle_url('/course/index.php'), URL_MATCH_BASE)) {
					$chelper->set_courses_display_option('viewmoreurl', new moodle_url($viewmoreurl, array('categoryid' => $coursecat->id)));
				}
			}
			$content .= $this->coursecat_courses($chelper, $courses, $coursecat->get_courses_count());
		}

		if ($showcoursesauto) {
			// restore the show_courses back to AUTO
			$chelper->set_show_courses(self::COURSECAT_SHOW_COURSES_AUTO);
		}

		return $content;
	}


	protected function coursecat_category(coursecat_helper $chelper, $coursecat, $depth, $boxColor="blue", $key=0) {
		//Get course image
		$courseImage = $this->getRandomImage($coursecat->get_courses());
		$content = '';

		// open category tag
		$classes = array('category');
		if (empty($coursecat->visible)) {
			$classes[] = 'dimmed_category';
		}
		if ($chelper->get_subcat_depth() > 0 && $depth >= $chelper->get_subcat_depth()) {
			// do not load content
			$categorycontent = '';
			$classes[] = 'notloaded';
			if ($coursecat->get_children_count() ||
				($chelper->get_show_courses() >= self::COURSECAT_SHOW_COURSES_COLLAPSED && $coursecat->get_courses_count())) {
				$classes[] = 'with_children';
				$classes[] = 'collapsed';
			}
		} else {
			$categorycontent = $this->coursecat_category_content($chelper, $coursecat, $depth);
			$classes[] = 'loaded';
			if (!empty($categorycontent)) {
				$classes[] = 'with_children';
				// Category content loaded with children.
				$this->categoryexpandedonload = true;
			}
		}

		// Make sure JS file to expand category content is included.
		$this->coursecat_include_js();

		$content .= html_writer::start_tag('div', array(
			'class' => join(' ', $classes) . ' cc-category-div-box',
			'data-categoryid' => $coursecat->id,
			'data-depth' => $depth,
			'data-i' => $key,
			'data-showcourses' => $chelper->get_show_courses(),
			'data-type' => self::COURSECAT_TYPE_CATEGORY));

		// Category name
		$categorynameText = $coursecat->get_formatted_name();
		$categorynameElement = '<div class="cc-category-background-color-'.$boxColor.'">';
		$categorynameElement .= '<div class="cc-category-label-container">';
		$categorynameElement .= html_writer::tag('div', $categorynameText, array('class' => 'cc-category-name'));

		if ($chelper->get_show_courses() == self::COURSECAT_SHOW_COURSES_COUNT) {
			$coursescount = $coursecat->get_courses_count();

			foreach($coursecat->get_children() as $subcatChild) {
				$coursescount += $subcatChild->get_courses_count();
			}

			$coursesLabel = ' CURSOS';
			if($coursescount == 1) {
				$coursesLabel = ' CURSO';
			}
			$categorynameElement .= html_writer::tag('span', $coursescount.$coursesLabel,
				array('title' => get_string('numberofcourses'), 'class' => 'cc-number-of-courses'));
			$categorynameElement .= '</div></div>';
		}

		$customStyle = 'background-color: #76879d;';
		if($courseImage != '') {
			$customStyle = 'background:url('.$courseImage.');';
		}

		$content .= html_writer::tag('div', $categorynameElement, array('style'=>$customStyle, 'class' => 'cc-category-image'));
		$content .= html_writer::end_tag('div'); // .category

		// Return the course category tree HTML
		return $content;
	}

	/**
	 * Renders html to display searchsearch result page
	 *
	 * @param array $searchcriteria may contain elements: search, blocklist, modulelist, tagid
	 * @return string
	 */
	public function search_courses($searchcriteria) {
		global $CFG;
		$content = '';
		if (!empty($searchcriteria)) {
			// print search results

			$displayoptions = array('sort' => array('displayname' => 1));
			// take the current page and number of results per page from query
			$perpage = optional_param('perpage', 0, PARAM_RAW);
			if ($perpage !== 'all') {
				$displayoptions['limit'] = ((int)$perpage <= 0) ? $CFG->coursesperpage : (int)$perpage;
				$page = optional_param('page', 0, PARAM_INT);
				$displayoptions['offset'] = $displayoptions['limit'] * $page;
			}
			// options 'paginationurl' and 'paginationallowall' are only used in method coursecat_courses()
			$displayoptions['paginationurl'] = new moodle_url('/course/search.php', $searchcriteria);
			$displayoptions['paginationallowall'] = true; // allow adding link 'View all'

			$class = 'course-search-result';
			foreach ($searchcriteria as $key => $value) {
				if (!empty($value)) {
					$class .= ' course-search-result-'. $key;
				}
			}
			$chelper = new coursecat_helper();
			$chelper->set_show_courses(self::COURSECAT_SHOW_COURSES_EXPANDED_WITH_CAT)->
			set_courses_display_options($displayoptions)->
			set_search_criteria($searchcriteria)->
			set_attributes(array('class' => $class));

			$courses = core_course_category::search_courses($searchcriteria, $chelper->get_courses_display_options());
			$totalcount = core_course_category::search_courses_count($searchcriteria);

			$courseslist = '';

			$current = 0;
			$rowCount = 0;
			$countForColor = 0;
			$courseslist .= '<div class="cc-all-cursos-list">';

			foreach($courses as $key=>$courseElement) {
				$countForColor++;
				if($rowCount == 0) {
					$courseslist .=	'<div class="row">';
				}
				$courseslist .=	'<div class="cc-all-cursos-block">
										<div class="cc-courses-image-container" style="background: url('. \theme_remui\utility::get_course_image($courseElement, 1) .');"></div>
											<div class="cc-courses-detail-container">
											'. $this->progressBarHTML($courseElement) .'
											<div class="cc-courses-cat-name">'. 'Lanzamiento: ' . $this->convertDateToSpanish($courseElement->startdate) .'</div>
												<div class="cc-courses-course-name">'. $courseElement->fullname .'</div>
												<a class="cc-courses-button" type="button" href="'. new moodle_url("/course/view.php",array("id" => $courseElement->id)). '">Acceder al curso</a>
											</div>
									</div>';
				$rowCount++;
				if($rowCount%3 == 0 && $rowCount<count($courses)) {
					$courseslist .=	'</div><div class="row">';
				} else if($rowCount >= count($courses)) {
					$courseslist .=	'</div>';
				}
				$current++;
			}
			$courseslist .= '</div>';

			//catalogo cursos EDIT
			//$courseslist = $this->coursecat_courses($chelper, $courses, $totalcount);

			if (!$totalcount) {
				if (!empty($searchcriteria['search'])) {
					$content .= $this->heading(get_string('nocoursesfound', '', $searchcriteria['search']));
				} else {
					$content .= $this->heading(get_string('novalidcourses'));
				}
			} else {
				$content .= '<h2 class="cc-big-header">Resultados de búsqueda</h2>';

				$encontro = 'encontró';
				$curso = 'curso';

				if($totalcount > 1) {
					$encontro = 'encontraron';
					$curso = 'cursos';
				}

				$content .= '<div class="cc-search-label-info">Se '. $encontro . ' ' .$totalcount . ' '. $curso . ' con la palabra "' . $searchcriteria['search'] . '"</div>';
				$content .= $courseslist;
				$content .=
					'<div class="cc-ultimos-cursos-button-div">
								<a class="cc-ultimos-cursos-button" type ="button" href="' . new moodle_url("/") . '">Ver categorías</a>
							</div>';
				$content .= $this->getUltimosCursos();
			}

//			Catalogo cursos - EDIT
//			if (!empty($searchcriteria['search'])) {
//				// print search form only if there was a search by search string, otherwise it is confusing
//				$content .= $this->box_start('generalbox mdl-align');
//				$content .= $this->course_search_form($searchcriteria['search']);
//				$content .= $this->box_end();
//			}

		}
		else {
// 			just print search form
//			$content .= $this->box_start('generalbox mdl-align');
//			$content .= $this->course_search_form();
//			$content .= $this->box_end();
			$content .= '<div style="text-align: center;">No se encontraron resultados relacionados a esta búsqueda</div>';
		}
		return $content;
	}

	/**
	 * Renders HTML to display particular course category - list of it's subcategories and courses
	 *
	 * Invoked from /course/index.php
	 *
	 * @param int|stdClass|core_course_category $category
	 */
	public function course_category($category) {
		global $CFG;
		$usertop = core_course_category::user_top();
		if (empty($category)) {
			$coursecat = $usertop;
		} else if (is_object($category) && $category instanceof core_course_category) {
			$coursecat = $category;
		} else {
			$coursecat = core_course_category::get(is_object($category) ? $category->id : $category);
		}
		$site = get_site();
		$output = '';

		if ($coursecat->can_create_course() || $coursecat->has_manage_capability()) {
			// Add 'Manage' button if user has permissions to edit this category.
			$managebutton = $this->single_button(new moodle_url('/course/management.php',
				array('categoryid' => $coursecat->id)), get_string('managecourses'), 'get');
			$this->page->set_button($managebutton);
		}

		if (core_course_category::is_simple_site()) {
			// There is only one category in the system, do not display link to it.
			$strfulllistofcourses = get_string('fulllistofcourses');
			$this->page->set_title("$site->shortname: $strfulllistofcourses");
		} else if (!$coursecat->id || !$coursecat->is_uservisible()) {
			$strcategories = get_string('categories');
			$this->page->set_title("$site->shortname: $strcategories");
		} else {
			$strfulllistofcourses = get_string('fulllistofcourses');
			$this->page->set_title("$site->shortname: $strfulllistofcourses");

			// Print the category selector
			$categorieslist = core_course_category::make_categories_list();
			if (count($categorieslist) > 1) {
				$output .= html_writer::start_tag('div', array('class' => 'categorypicker'));
				$select = new single_select(new moodle_url('/course/index.php'), 'categoryid',
					core_course_category::make_categories_list(), $coursecat->id, null, 'switchcategory');
				$select->set_label(get_string('categories').':');
				$output .= $this->render($select);
				$output .= html_writer::end_tag('div'); // .categorypicker
			}
		}

		// Print current category description
		$chelper = new coursecat_helper();
		if ($description = $chelper->get_category_formatted_description($coursecat)) {
			$output .= $this->box($description, array('class' => 'generalbox info'));
		}

		// Prepare parameters for courses and categories lists in the tree
		$chelper->set_show_courses(self::COURSECAT_SHOW_COURSES_AUTO)
			->set_attributes(array('class' => 'category-browse category-browse-'.$coursecat->id));

		$coursedisplayoptions = array();
		$catdisplayoptions = array();
		$browse = optional_param('browse', null, PARAM_ALPHA);
		$perpage = optional_param('perpage', $CFG->coursesperpage, PARAM_INT);
		$page = optional_param('page', 0, PARAM_INT);
		$baseurl = new moodle_url('/course/index.php');
		if ($coursecat->id) {
			$baseurl->param('categoryid', $coursecat->id);
		}
		if ($perpage != $CFG->coursesperpage) {
			$baseurl->param('perpage', $perpage);
		}
		$coursedisplayoptions['limit'] = $perpage;
		$catdisplayoptions['limit'] = $perpage;
		if ($browse === 'courses' || !$coursecat->get_children_count()) {
			$coursedisplayoptions['offset'] = $page * $perpage;
			$coursedisplayoptions['paginationurl'] = new moodle_url($baseurl, array('browse' => 'courses'));
			$catdisplayoptions['nodisplay'] = true;
			$catdisplayoptions['viewmoreurl'] = new moodle_url($baseurl, array('browse' => 'categories'));
			$catdisplayoptions['viewmoretext'] = new lang_string('viewallsubcategories');
		} else if ($browse === 'categories' || !$coursecat->get_courses_count()) {
			$coursedisplayoptions['nodisplay'] = true;
			$catdisplayoptions['offset'] = $page * $perpage;
			$catdisplayoptions['paginationurl'] = new moodle_url($baseurl, array('browse' => 'categories'));
			$coursedisplayoptions['viewmoreurl'] = new moodle_url($baseurl, array('browse' => 'courses'));
			$coursedisplayoptions['viewmoretext'] = new lang_string('viewallcourses');
		} else {
			// we have a category that has both subcategories and courses, display pagination separately
			$coursedisplayoptions['viewmoreurl'] = new moodle_url($baseurl, array('browse' => 'courses', 'page' => 1));
			$catdisplayoptions['viewmoreurl'] = new moodle_url($baseurl, array('browse' => 'categories', 'page' => 1));
		}
		$chelper->set_courses_display_options($coursedisplayoptions)->set_categories_display_options($catdisplayoptions);
		// Add course search form.
		//$output .= $this->course_search_form();

		// Display course category tree.
		$output .= $this->getUltimosCursos(true);

		//catalogo cursos - EDIT
		// Add action buttons
//		$output .= $this->container_start('buttons');
//		if ($coursecat->is_uservisible()) {
//			$context = get_category_or_system_context($coursecat->id);
//			if (has_capability('moodle/course:create', $context)) {
//				// Print link to create a new course, for the 1st available category.
//				if ($coursecat->id) {
//					$url = new moodle_url('/course/edit.php', array('category' => $coursecat->id, 'returnto' => 'category'));
//				} else {
//					$url = new moodle_url('/course/edit.php',
//						array('category' => $CFG->defaultrequestcategory, 'returnto' => 'topcat'));
//				}
//				$output .= $this->single_button($url, get_string('addnewcourse'), 'get');
//			}
//			ob_start();
//			print_course_request_buttons($context);
//			$output .= ob_get_contents();
//			ob_end_clean();
//		}
		$output .= $this->container_end();
		return $output;
	}

	/**
	 * Returns HTML to print list of available courses for the frontpage
	 *
	 * @return string
	 */
	public function frontpage_available_courses() {
		global $CFG;

		$chelper = new coursecat_helper();
		$chelper->set_show_courses(self::COURSECAT_SHOW_COURSES_EXPANDED)->set_courses_display_options(array(
			'recursive' => true,
			'limit' => $CFG->frontpagecourselimit,
			'viewmoreurl' => new moodle_url('/course/index.php'),
			'viewmoretext' => new lang_string('fulllistofcourses')));

		$chelper->set_attributes(array('class' => 'frontpage-course-list-all'));
		$courses = core_course_category::get(0)->get_courses($chelper->get_courses_display_options());
		$totalcount = core_course_category::get(0)->get_courses_count($chelper->get_courses_display_options());
		if (!$totalcount && !$this->page->user_is_editing() && has_capability('moodle/course:create', \context_system::instance())) {
			// Print link to create a new course, for the 1st available category.
			return $this->add_new_course_button();
		}
		$latestcard = get_config('theme_remui', 'enablenewcoursecards');
		$coursehtml = '<div class=""><div class="card-deck slick-course-slider slick-slider ' . ($latestcard ? 'latest-cards' : '') . '">';
		// $courses = array_slice($courses, count($courses) - 10);
		if (!empty($courses)) {
			foreach ($courses as $course) {
				$coursesummary = strip_tags($chelper->get_course_formatted_summary($course, array('overflowdiv' => false, 'noclean' => false, 'para' => false)));
				$coursesummary = strlen($coursesummary) > 100 ? substr($coursesummary, 0, 100)."..." : $coursesummary;
				$image = \theme_remui\utility::get_course_image($course, 1);
				$coursename = strip_tags($chelper->get_course_formatted_name($course));
				if (!$latestcard) {
					$coursehtml .= "
                    <div class='card w-100 rounded-bottom mx-0 bg-transparent d-inline-flex flex-column' style='height: 100%;'>
                        <div class='m-2 bg-white border' style='height: 100%;'>
                            <div class='rounded-top' style='height: 200px;
                            background-image: url({$image});background-size: cover;background-position: center; box-shadow: 0 2px 5px #cccccc;'>
                            </div>
                            <div class='card-body p-3'>
                                <h4 class='card-title m-1 ellipsis ellipsis-2'>
                                    <a href='{$CFG->wwwroot}/course/view.php?id={$course->id}' class='font-weight-400 blue-grey-600 font-size-18'>
                                        {$coursename}
                                    </a>
                                </h4>
                                <p class='card-text m-1'>{$coursesummary}</p>
                            </div>
                        </div>
                    </div>";
				} else {
					if (isset($course->startdate)) {
						$startdate = date('d M, Y', $course->startdate);
						$day = substr($startdate, 0, 2);
						$month = substr($startdate, 3, 3);
						$year = substr($startdate, 8, 4);
					}
					$coursehtml .= "
                    <div class='px-1 course_card card '>
                        <div class='wrapper h-100' style='background-image: url({$image});background-size: cover;background-position: center;position: relative;'>
                            <div class='date btn-primary'>
                                <span class='day'>{$day}</span>
                                <span class='month'>{$month}</span>
                                <span class='year'>{$year}</span>
                            </div>
                            <div class='data'>
                                <div class='content' title='Vestibulum purus quam scelerisque ut'>
                                    <span class='author'>Miscellaneous</span>
                                    <h4 class='title'><a href='{$CFG->wwwroot}/course/view.php?id={$course->id}'>{$coursename}</a></h4>
                                    <p class='text'>{$coursesummary}</p>
                                </div>
                            </div>
                        </div>
                    </div>";
				}
			}
		}

		$coursehtml .= '</div></div>';

		$coursehtml .= " <div class='available-courses button-container w-100 text-center '>
            <button type='button' class='btn btn-floating btn-primary btn-prev btn-sm'><i class='icon fa fa-chevron-left' aria-hidden='true'></i></button>
            <button type='button' class='btn btn-floating btn-primary btn-next btn-sm'><i class='icon fa fa-chevron-right' aria-hidden='true'></i></button>
        </div>";

		$coursehtml .= "
        <div class='row'>
            <div class='col-12 text-right'>
                <a href='{$CFG->wwwroot}/course' class='btn btn-primary'>" . get_string('viewallcourses', 'core')."</a>
            </div>
        </div>";

		return '';
	}

}
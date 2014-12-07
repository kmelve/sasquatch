<?php
/*
Plugin Name: Sasquatch
Plugin URI: https://github.com/kmelve/sasquatch
Version: 0.1
Author: Knut Melvær
Description: Converts Word generated html with footnotes to bigfootjs compatible ones.
*/
/*
TODO
-   Allow for line or paragraph breaks in footnote text
-   Generate random\unique ID-numbers
 */


function sasquatch($content_pre){
    $pattern_ftnref = '/<a href.+?ftnref.+name.+ftn\d+.+\n/i'; /* Find the footnotes. Line or paragraph breaks will break the stuff. Gotta fix that somehow */
    preg_match_all($pattern_ftnref, $content_pre, $footnotelist); /* Take the footnotes and put them into an array */
    $footnotelist = call_user_func_array('array_merge', $footnotelist); /* Fix the multidimensional array*/

    $pattern_ftn = '/<a.+ftn(\d+).+name.+ftnref.+<\/a>/i'; /* First fix the text footnote numbers */
    $replacement_ftn = '<sup id="fnref:$1"><a href="#fn:$1" rel="footnote">$1</a></sup>'; /* Replacement pattern */
    $content_pre = preg_replace($pattern_ftn, $replacement_ftn, $content_pre); /* Replace with this bigfoot.js compapitble html */
    $content_post = preg_split($pattern_ftnref, $content_pre); /* Split and put the content into an array */
    $content_post = $content_post[0]; /* Start with the post content */
    $content_post .= '<div class="footnote"><hr /><ol>'; /* Append markup for the footnote list */

    $counter = 0; /* Counters, gotta have em (to skip the first item in the array). */
    $pattern_ftlistn = '/<a href.+?ftnref.+name?.+<\/a>/i';

    foreach($footnotelist as $footnote) {
        if ($counter++ > 1) continue; /* Skip the first item, 'cause that's where the main content is */
        $id = $counter; /* generate footnote IDs */
        $footnote = preg_replace($pattern_ftlistn, '', $footnote); /* Strip the footnote numbers brackets */
        $footnote = preg_replace('/\r|\n/', '', $footnote); /* Strip the footnote of linebreaks */
        $content_post .= '<li id="fn:' . $id . '">' . $footnote . '<a href="#fnref:' . $id . '" title="return to article" class="reversefootnote">&#160;&#8617;</a></li>'; /* Put and append the footnotes into a list.*/
    }
    $content_post .= '</ol></div>'; /* Append the closing tags */

    return $content_post; /* Return the fresh html. Ready for Bigfoot.js to do its work (also, just nicer) */
}
add_filter('content_save_pre','sasquatch');
?>

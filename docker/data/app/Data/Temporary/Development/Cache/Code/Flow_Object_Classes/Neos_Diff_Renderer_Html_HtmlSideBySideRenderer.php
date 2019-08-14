<?php 
namespace Neos\Diff\Renderer\Html;

/**
 * This file is part of the Neos.Diff package.
 *
 * (c) 2009 Chris Boulton <chris.boulton@interspire.com>
 * Portions (c) Contributors of the Neos Project - www.neos.io
 *
 * This package is Open Source Software. For the full copyright and license
 * information, please view the LICENSE file which was distributed with this
 * source code.
 */

/**
 * Inline HTML Diff Renderer
 */
class HtmlSideBySideRenderer_Original extends HtmlArrayRenderer
{
    /**
     * Render a and return diff with changes between the two sequences
     * displayed side by side.
     *
     * @return string The generated side by side diff.
     */
    public function render()
    {
        $changes = parent::render();

        $html = '';
        if (empty($changes)) {
            return $html;
        }

        $html .= '<table class="Differences DifferencesSideBySide">';
        $html .= '<thead>';
        $html .= '<tr>';
        $html .= '<th colspan="2">Old Version</th>';
        $html .= '<th colspan="2">New Version</th>';
        $html .= '</tr>';
        $html .= '</thead>';
        foreach ($changes as $i => $blocks) {
            if ($i > 0) {
                $html .= '<tbody class="Skipped">';
                $html .= '<th>&hellip;</th><td>&nbsp;</td>';
                $html .= '<th>&hellip;</th><td>&nbsp;</td>';
                $html .= '</tbody>';
            }

            foreach ($blocks as $change) {
                $html .= '<tbody class="Change' . ucfirst($change['tag']) . '">';
                // Equal changes should be shown on both sides of the diff
                if ($change['tag'] == 'equal') {
                    foreach ($change['base']['lines'] as $no => $line) {
                        $fromLine = $change['base']['offset'] + $no + 1;
                        $toLine = $change['changed']['offset'] + $no + 1;
                        $html .= '<tr>';
                        $html .= '<th>' . $fromLine . '</th>';
                        $html .= '<td class="Left"><span>' . $line . '</span>&nbsp;</span></td>';
                        $html .= '<th>' . $toLine . '</th>';
                        $html .= '<td class="Right"><span>' . $line . '</span>&nbsp;</span></td>';
                        $html .= '</tr>';
                    }
                } // Added lines only on the right side
                else {
                    if ($change['tag'] == 'insert') {
                        foreach ($change['changed']['lines'] as $no => $line) {
                            $toLine = $change['changed']['offset'] + $no + 1;
                            $html .= '<tr>';
                            $html .= '<th>&nbsp;</th>';
                            $html .= '<td class="Left">&nbsp;</td>';
                            $html .= '<th>' . $toLine . '</th>';
                            $html .= '<td class="Right"><ins>' . $line . '</ins>&nbsp;</td>';
                            $html .= '</tr>';
                        }
                    } // Show deleted lines only on the left side
                    else {
                        if ($change['tag'] == 'delete') {
                            foreach ($change['base']['lines'] as $no => $line) {
                                $fromLine = $change['base']['offset'] + $no + 1;
                                $html .= '<tr>';
                                $html .= '<th>' . $fromLine . '</th>';
                                $html .= '<td class="Left"><del>' . $line . '</del>&nbsp;</td>';
                                $html .= '<th>&nbsp;</th>';
                                $html .= '<td class="Right">&nbsp;</td>';
                                $html .= '</tr>';
                            }
                        } // Show modified lines on both sides
                        else {
                            if ($change['tag'] == 'replace') {
                                if (count($change['base']['lines']) >= count($change['changed']['lines'])) {
                                    foreach ($change['base']['lines'] as $no => $line) {
                                        $fromLine = $change['base']['offset'] + $no + 1;
                                        $html .= '<tr>';
                                        $html .= '<th>' . $fromLine . '</th>';
                                        $html .= '<td class="Left"><span>' . $line . '</span>&nbsp;</td>';
                                        if (!isset($change['changed']['lines'][$no])) {
                                            $toLine = '&nbsp;';
                                            $changedLine = '&nbsp;';
                                        } else {
                                            $toLine = $change['base']['offset'] + $no + 1;
                                            $changedLine = '<span>' . $change['changed']['lines'][$no] . '</span>';
                                        }
                                        $html .= '<th>' . $toLine . '</th>';
                                        $html .= '<td class="Right">' . $changedLine . '</td>';
                                        $html .= '</tr>';
                                    }
                                } else {
                                    foreach ($change['changed']['lines'] as $no => $changedLine) {
                                        if (!isset($change['base']['lines'][$no])) {
                                            $fromLine = '&nbsp;';
                                            $line = '&nbsp;';
                                        } else {
                                            $fromLine = $change['base']['offset'] + $no + 1;
                                            $line = '<span>' . $change['base']['lines'][$no] . '</span>';
                                        }
                                        $html .= '<tr>';
                                        $html .= '<th>' . $fromLine . '</th>';
                                        $html .= '<td class="Left"><span>' . $line . '</span>&nbsp;</td>';
                                        $toLine = $change['changed']['offset'] + $no + 1;
                                        $html .= '<th>' . $toLine . '</th>';
                                        $html .= '<td class="Right">' . $changedLine . '</td>';
                                        $html .= '</tr>';
                                    }
                                }
                            }
                        }
                    }
                }
                $html .= '</tbody>';
            }
        }
        $html .= '</table>';
        return $html;
    }
}

#
# Start of Flow generated Proxy code
#
namespace Neos\Diff\Renderer\Html;

use Doctrine\ORM\Mapping as ORM;
use Neos\Flow\Annotations as Flow;

/**
 * Inline HTML Diff Renderer
 */
class HtmlSideBySideRenderer extends HtmlSideBySideRenderer_Original implements \Neos\Flow\ObjectManagement\Proxy\ProxyInterface {

    use \Neos\Flow\ObjectManagement\Proxy\ObjectSerializationTrait;


    /**
     * Autogenerated Proxy Method
     */
    public function __sleep()
    {
            $result = NULL;
        $this->Flow_Object_PropertiesToSerialize = array();

        $transientProperties = array (
);
        $propertyVarTags = array (
  'defaultOptions' => 'array Array of the default options that apply to this renderer.',
  'diff' => 'object Instance of the diff class that this renderer is generating the rendered diff for.',
  'options' => 'array Array containing the user applied and merged default options for the renderer.',
);
        $result = $this->Flow_serializeRelatedEntities($transientProperties, $propertyVarTags);
        return $result;
    }

    /**
     * Autogenerated Proxy Method
     */
    public function __wakeup()
    {

        $this->Flow_setRelatedEntities();
    }
}
# PathAndFilename: /var/www/html/neos/Packages/Application/Neos.Diff/Classes/Renderer/Html/HtmlSideBySideRenderer.php
#
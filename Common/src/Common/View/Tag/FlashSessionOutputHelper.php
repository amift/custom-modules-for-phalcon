<?php

namespace Common\View\Tag;

use Phalcon\Mvc\User\Component;

class FlashSessionOutputHelper extends Component
{

    private $_mask = '
        <div class="alert%s_cont">
            <div class="sp12"></div>
            <div class="alert alert-%s" role="alert">
                <button type="button" class="close" data-onclick-remove=".alert%s_cont"><span aria-hidden="true">&times;</span></button>
                <div>%s</div>
            </div>
        </div>';

    private $_classes = [
        'error'     => 'alert alert-danger',
        'success'   => 'alert alert-success',
        'notice'    => 'alert alert-info',
        'warning'   => 'alert alert-warning'
    ];

    public function render()
    {
        $html = '';

        $allMessages = $this->di->getFlashSession()->getMessages();
        if (count($allMessages) > 0) {
            $counter = 1;
            foreach ($allMessages as $type => $messages) {
                foreach ($messages as $message) {
                    $class = isset($this->_classes[$type]) ? $this->_classes[$type] : $type;
                    $html .= sprintf($this->_mask, $counter, $class, $counter, $message);
                    $counter++;
                }
            }
        }

        return $html;
    }

}

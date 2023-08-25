<?php

declare(strict_types=1);

namespace WeDevelop\ElementalWidget\UserForm\Controller;

use gorriecoe\Link\Models\Link;
use SilverStripe\Core\ClassInfo;
use WeDevelop\ElementalWidget\Element\ElementWidget;
use WeDevelop\ElementalWidget\UserForm\Extension\SubmissionLimitExtension;
use WeDevelop\ElementalWidget\UserForm\Widget\UserFormWidget;
use SilverStripe\CMS\Model\SiteTree;
use SilverStripe\Control\Controller;
use SilverStripe\Control\Director;
use SilverStripe\Forms\HiddenField;
use SilverStripe\UserForms\Control\UserDefinedFormController;
use SilverStripe\View\Requirements;
use WeDevelop\ElementalWidget\UserForm\Extension\SubmissionCountExtension;
use WeDevelop\ElementalWidget\UserForm\Handler\AbstractHandler;

class UserFormWidgetController extends UserDefinedFormController
{
    /** @config */
    private static string $url_segment = 'element-user-form-controller';

    /**
     * @var array<string>
     * @config
     */
    private static array $allowed_actions = [
        'Form',
    ];

    private static array $url_handlers = [
        '$WidgetId/$ElementID/Form' => 'Form',
    ];

    private ?UserFormWidget $widget = null;
    private ?ElementWidget $element = null;

    public function __construct($dataRecord = null)
    {
        $this->widget = $dataRecord;

        Requirements::javascript('//code.jquery.com/jquery-3.6.0.min.js');
        Requirements::javascript(
            'silverstripe/userforms:client/dist/js/jquery-validation/jquery.validate.min.js'
        );
        Requirements::javascript('silverstripe/admin:client/dist/js/i18n.js');
        Requirements::add_i18n_javascript('silverstripe/userforms:client/lang');
        Requirements::javascript('silverstripe/userforms:client/dist/js/userforms.js');

        if (array_key_exists('WidgetID', $_POST)) {
            $this->widget = UserFormWidget::get()->byID($_POST['WidgetID']);
        }

        if (array_key_exists('ElementID', $_POST)) {
            $this->element = ElementWidget::get()->byID($_POST['ElementID']);
        } else {
            $this->element = $this->widget?->getElement();
        }

        if (!$this->widget) {
            $this->setWidgetAndElementFromUrl($_SERVER['REQUEST_URI']);
        }

        parent::__construct($this->widget);
    }

    private function setWidgetAndElementFromUrl(string $url): void
    {
        $requestParts = explode('/', $url);
        if (is_int($urlSegmentIndex = array_search(self::$url_segment, $requestParts))) {
            $widgetId = $requestParts[$urlSegmentIndex+1];
            $elementId = $requestParts[$widgetId+1];
            $this->widget = UserFormWidget::get()->byID($widgetId);
            $this->element = ElementWidget::get()->byID($elementId);
        }
    }

    public function Link($action = null)
    {
        if (!$this->widget || !$this->element) {
            $this->setWidgetAndElementFromUrl($this->getRequest()->getURL());
        }
        
        return Controller::join_links(Director::baseURL(), self::$url_segment, $this->widget->ID, $this->element->ID, $action);
    }

    public function Form()
    {
        $form = parent::Form();

        /** @var SiteTree $page */
        $page = $this->element->getPage();
        $form->Fields()->push(HiddenField::create('_PageUrl', '_PageUrl', $page->Link()));

        if (!$this->widget) {
            $this->widget =  UserFormWidget::get()->byID($this->getRequest()->param('WidgetID'));
        }

        if ($this->widget) {
            $form->Fields()->push(HiddenField::create('WidgetID', 'WidgetID', $this->widget->ID));
        }

        if ($this->element) {
            $form->Fields()->push(HiddenField::create('ElementID', 'ElementID', $this->element->ID));
            $form->Actions()->dataFieldByName('action_process')->addExtraClass($this->element->SubmitButtonColor);
        }

        return $form;
    }

    public function process($data, $form)
    {
        if (!$this->widget instanceof UserFormWidget) {
            throw new \Exception('Widget not found');
        }

        if ($this->widget->hasExtension(SubmissionLimitExtension::class) && $this->widget->LimitReached()) {
            $form->sessionError('Maximaal aantal is bereikt.');

            $controller = Controller::curr();

            if (!$controller) {
                throw new \RuntimeException('Expected a set controller but found none.');
            }

            return $controller->redirectBack();
        }

        if ($this->widget->hasExtension(SubmissionCountExtension::class)) {
            /* Submission counting */
            $this->widget->SubmissionsCountDay++;
            $this->widget->SubmissionsCountWeek++;
            $this->widget->SubmissionsCountMonth++;
            $this->widget->write();
        }

        $oldRedirect = parent::process($data, $form);
        $oldRedirect->removeHeader('Location');

        if ($this->widget) {
            $request = $this->getRequest();

            foreach (ClassInfo::subclassesFor(AbstractHandler::class) as $handlerClass) {
                if ($handlerClass === AbstractHandler::class) {
                    continue;
                }

                if ($handlerClass::supports($this->widget, $data, $request)) {
                    $handlerClass::handle($this->widget, $data, $request);
                }
            }
        }

        /** @var ?Link $successLink */
        $successLink = $this->element->Widget()->SuccessPage();

        return $this->redirect($successLink->getLinkURL());
    }
}

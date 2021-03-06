<?php
namespace Craft;

/**
 * Class QuickPostWidget
 *
 * @author    Pixel & Tonic, Inc. <support@pixelandtonic.com>
 * @copyright Copyright (c) 2014, Pixel & Tonic, Inc.
 * @license   http://buildwithcraft.com/license Craft License Agreement
 * @see       http://buildwithcraft.com
 * @package   craft.app.widgets
 * @since     1.0
 */
class QuickPostWidget extends BaseWidget
{
	// Properties
	// =========================================================================

	/**
	 * @var bool
	 */
	public $multipleInstances = true;

	/**
	 * @var
	 */
	private $_section;

	// Public Methods
	// =========================================================================

	/**
	 * Returns the type of widget this is.
	 *
	 * @return string
	 */
	public function getName()
	{
		return Craft::t('Quick Post');
	}

	/**
	 * Returns the widget's body HTML.
	 *
	 * @return string
	 */
	public function getSettingsHtml()
	{
		// Find the sections the user has permission to create entries in
		$sections = array();

		foreach (craft()->sections->getAllSections() as $section)
		{
			if ($section->type !== SectionType::Single)
			{
				if (craft()->userSession->checkPermission('createEntries:'.$section->id))
				{
					$sections[] = $section;
				}
			}
		}

		return craft()->templates->render('_components/widgets/QuickPost/settings', array(
			'sections' => $sections,
			'settings' => $this->getSettings()
		));
	}

	/**
	 * Preps the settings before they're saved to the database.
	 *
	 * @param array $settings
	 *
	 * @return array
	 */
	public function prepSettings($settings)
	{
		$sectionId = $settings['section'];

		if (isset($settings['sections']))
		{
			if (isset($settings['sections'][$sectionId]))
			{
				$settings = array_merge($settings, $settings['sections'][$sectionId]);
			}

			unset($settings['sections']);
		}

		return $settings;
	}

	/**
	 * Gets the widget's title.
	 *
	 * @return string
	 */
	public function getTitle()
	{
		if (craft()->getEdition() >= Craft::Client)
		{
			$section = $this->_getSection();

			if ($section)
			{
				return Craft::t('Post a new {section} entry', array('section' => $section->name));
			}
		}

		return $this->getName();
	}

	/**
	 * Returns the widget's body HTML.
	 *
	 * @return string|false
	 */
	public function getBodyHtml()
	{
		craft()->templates->includeTranslations('Entry saved.', 'Couldn’t save entry.');
		craft()->templates->includeJsResource('js/QuickPostWidget.js');

		$section = $this->_getSection();

		if (!$section)
		{
			return '<p>'.Craft::t('No section has been selected yet.').'</p>';
		}

		$entryTypes = $section->getEntryTypes('id');

		if (!$entryTypes)
		{
			return '<p>'.Craft::t('No entry types exist for this section.').'</p>';
		}

		if ($this->getSettings()->entryType && isset($entryTypes[$this->getSettings()->entryType]))
		{
			$entryTypeId = $this->getSettings()->entryType;
		}
		else
		{
			$entryTypeId = array_shift(array_keys($entryTypes));
		}

		$entryType = $entryTypes[$entryTypeId];

		$params = array(
			'sectionId'   => $section->id,
			'typeId' => $entryTypeId,
		);

		craft()->templates->startJsBuffer();

		$html = craft()->templates->render('_components/widgets/QuickPost/body', array(
			'section'   => $section,
			'entryType' => $entryType,
			'settings'  => $this->getSettings()
		));

		$fieldJs = craft()->templates->clearJsBuffer(false);

		craft()->templates->includeJs('new Craft.QuickPostWidget(' .
			$this->model->id.', ' .
			JsonHelper::encode($params).', ' .
			"function() {\n".$fieldJs .
		"\n});");

		return $html;
	}

	// Protected Methods
	// =========================================================================

	/**
	 * Defines the settings.
	 *
	 * @return array
	 */
	protected function defineSettings()
	{
		return array(
			'section'   => array(AttributeType::Number, 'required' => true),
			'entryType' => AttributeType::Number,
			'fields'    => AttributeType::Mixed,
		);
	}

	// Private Methods
	// =========================================================================

	/**
	 * Returns the widget's section.
	 *
	 * @return SectionModel|false
	 */
	private function _getSection()
	{
		if (!isset($this->_section))
		{
			$this->_section = false;

			$sectionId = $this->getSettings()->section;

			if ($sectionId)
			{
				$this->_section = craft()->sections->getSectionById($sectionId);
			}
		}

		return $this->_section;
	}
}

<?php
/**
* @package		EasySocial
* @copyright	Copyright (C) 2010 - 2012 Stack Ideas Sdn Bhd. All rights reserved.
* @license		GNU/GPL, see LICENSE.php
* EasySocial is free software. This version may have been modified pursuant
* to the GNU General Public License, and as distributed it includes or
* is derivative of works licensed under the GNU General Public License or
* other free or open source software licenses.
* See COPYRIGHT.php for copyright notices and details.
*/
defined( '_JEXEC' ) or die( 'Unauthorized Access' );

// Include the fields library
Foundry::import( 'admin:/includes/fields/fields' );

// Include helper lib
Foundry::import( 'fields:/user/joomla_email/helper' );

/**
 * Field application for Joomla email
 *
 * @since	1.0
 * @author	Mark Lee <mark@stackideas.com>
 */
class SocialFieldsUserJoomla_Email extends SocialFieldItem
{
	/**
	 * Class constructor.
	 *
	 * @since	1.0
	 * @access	public
	 */
	public function __construct( $config = array() )
	{
		parent::__construct( $config );
	}

	/**
	 * Displays the field input for user when they register their account.
	 *
	 * @since	1.0
	 * @access	public
	 * @param	array
	 * @param	SocialTableRegistration
	 * @return	string	The html output.
	 *
	 * @author	Mark Lee <mark@stackideas.com>
	 */
	public function onRegister( &$post , &$registration )
	{
		// Try to check to see if user has already set the username.
		$email	= !empty( $post[ 'email' ] ) ? $post[ 'email' ] : '';

		// Set the username property for the theme.
		$this->set( 'email'	, $this->escape( $email ) );

		// Detect if there's any errors.
		$error 	= $registration->getErrors( $this->inputName );

		$this->set( 'error'	, $error );

		// Output the registration template.
		return $this->display();
	}

	/**
	 * Determines whether there's any errors in the submission in the normal form.
	 *
	 * @since	1.0
	 * @access	public
	 * @param	array 	The posted data.
	 * @return	bool	Determines if the system should proceed or throw errors.
	 *
	 * @author	Mark Lee <mark@stackideas.com>
	 */
	public function onRegisterValidate( &$post )
	{
		return $this->validateEmail( $post );
	}

	/**
	 * Save trigger before user object is saved
	 *
	 * @since	1.0
	 * @access	public
	 * @param	array 	The posted data.
	 * @param	SocialUser	The user object.
	 * @return	bool	State of the trigger
	 *
	 * @author	Jason Rey <jasonrey@stackideas.com>
	 */
	public function onRegisterBeforeSave( &$post, &$user )
	{
		// Set the email address into the user object
		$user->set( 'email', $post['email'] );

		$config = Foundry::config();

		// If settings is set to use email as username, then we parse the email through to username
		if( $config->get( 'registrations.emailasusername' ) )
		{
			$post['username'] = $post['email'];
		}

		// Unset the email address from the post data
		unset( $post['email'] );

		return true;
	}

	/**
	 * Responsible to output the html codes that is displayed to
	 * a user when they edit their profile.
	 *
	 * @since	1.0
	 * @access	public
	 * @param	SocialUser	The user object that is being edited.
	 * @param	Array		The posted data.
	 * @param	Array		An array consisting of errors.
	 */
	public function onEdit( &$post, &$user, $errors )
	{
		$app 	= JFactory::getApplication();

		$email 	= isset( $post[ 'email' ] ) ? $post[ 'email' ] : $user->email;

		$this->set( 'email', $this->escape( $email ) );

		// Determine if the error state should appear.
		$error = $this->getError( $errors );
		$this->set( 'error' , $error );

		return $this->display();
	}

	/**
	 * Determines whether there's any errors in the submission in the registration form.
	 *
	 * @since	1.0
	 * @access	public
	 * @param	array 	The posted data.
	 * @return	bool	Determines if the system should proceed or throw errors.
	 *
	 * @author	Mark Lee <mark@stackideas.com>
	 */
	public function onEditValidate( &$post, &$user )
	{
		return $this->validateEmail( $post , $user->email );
	}

	/**
	 * Save trigger before user object is saved
	 *
	 * @since	1.0
	 * @access	public
	 * @param	array 	The posted data.
	 * @param	SocialUser	The user object.
	 * @return	bool	State of the trigger
	 *
	 * @author	Jason Rey <jasonrey@stackideas.com>
	 */
	public function onEditBeforeSave( &$post, &$user )
	{
		// Set the email address into the user object
		$user->set( 'email', $post['email'] );

		$config = Foundry::config();

		// If settings is set to use email as username, then we parse the email through to username
		if( $config->get( 'registrations.emailasusername' ) )
		{
			$post['username'] = $post['email'];
		}

		// Unset the email address from the post data
		unset( $post['email'] );

		return true;
	}

	/**
	 * Displays the sample html codes when the field is added into the profile.
	 *
	 * @since	1.0
	 * @access	public
	 * @param	array
	 * @return	string	The html output.
	 *
	 * @author	Mark Lee <mark@stackideas.com>
	 */
	public function onSample()
	{
		return $this->display();
	}

	/**
	 * Validates the posted email
	 *
	 * @since	1.0
	 * @access	private
	 * @param	string
	 * @return	bool		True if valid, false otherwise.
	 */
	private function validateEmail( &$post , $currentEmail = '' )
	{
		$email      = !empty( $post[ 'email' ] ) ? trim( $post[ 'email' ] ) : '';

		// Check for email validity
		if( !SocialFieldsUserJoomlaEmailHelper::isValid( $email ) )
		{
			$this->setError( JText::_( 'PLG_FIELDS_JOOMLA_EMAIL_VALIDATION_INVALID_EMAIL' ) );
			return false;
		}

		// Check for allowed domains
		if( !SocialFieldsUserJoomlaEmailHelper::isAllowed( $email, $this->params ) )
		{
			return $ajax->reject( JText::_( 'PLG_FIELDS_JOOMLA_EMAIL_VALIDATION_DOMAIN_IS_NOT_ALLOWED' ) );
		}

		// Check for disallowed domains
		if( SocialFieldsUserJoomlaEmailHelper::isDisallowed( $email , $this->params ) )
		{
			$this->setError( JText::_( 'PLG_FIELDS_JOOMLA_EMAIL_VALIDATION_DOMAIN_IS_DISALLOWED' ) );
			return false;
		}

		// Check for forbidden words
		if( SocialFieldsUserJoomlaEmailHelper::isForbidden( $email , $this->params ) )
		{
			$this->setError( JText::_( 'PLG_FIELDS_JOOMLA_EMAIL_VALIDATION_CONTAINS_FORBIDDEN' ) );
			return false;
		}

		// Check if current email exist
		if( SocialFieldsUserJoomlaEmailHelper::exists( $email , $currentEmail ) )
		{
			$this->setError( JText::_( 'PLG_FIELDS_JOOMLA_EMAIL_VALIDATION_ALREADY_USED' ) );
			return false;
		}

		return true;
	}


	/**
	 * return formated string from the fields value
	 *
	 * @since	1.0
	 * @access	public
	 * @param	userfielddata
	 * @return	array array of objects with two attribute, ffriend_id, score
	 *
	 * @author	Mark Lee <mark@stackideas.com>
	 */
	public function onIndexer( $userFieldData )
	{
		if(! $this->field->searchable )
			return false;

		$content = trim( $userFieldData );
		if( $content )
			return $content;
		else
			return false;
	}

	/**
	 * return formated string from the fields value
	 *
	 * @since	1.0
	 * @access	public
	 * @param	userfielddata
	 * @return	array array of objects with two attribute, ffriend_id, score
	 *
	 * @author	Mark Lee <mark@stackideas.com>
	 */
	public function onIndexerSearch( $itemCreatorId, $keywords, $userFieldData )
	{
		if(! $this->field->searchable )
			return false;

		$data 		= trim( $userFieldData );

		$content 			= '';
		if( JString::stristr( $data, $keywords ) !== false )
		{
			$content = $data;
		}

		if( $content )
		{
			$my = Foundry::user();
			$privacyLib = Foundry::privacy( $my->id );

			if( ! $privacyLib->validate( 'core.view', $this->field->id, SOCIAL_TYPE_FIELD, $itemCreatorId ) )
			{
				return -1;
			}
			else
			{
				// okay this mean the user can view this fields. let hightlight the content.

				// building the pattern for regex replace
				$searchworda	= preg_replace('#\xE3\x80\x80#s', ' ', $keywords);
				$searchwords	= preg_split("/\s+/u", $searchworda);
				$needle			= $searchwords[0];
				$searchwords	= array_unique($searchwords);

				$pattern	= '#(';
				$x 			= 0;

				foreach ($searchwords as $k => $hlword)
				{
					$pattern 	.= $x == 0 ? '' : '|';
					$pattern	.= preg_quote( $hlword , '#' );
					$x++;
				}
				$pattern 		.= ')#iu';

				$content 	= preg_replace( $pattern , '<span class="search-highlight">\0</span>' , $content );
				$content 	= JText::sprintf( 'PLG_FIELDS_JOOMLA_EMAIL_SEARCH_RESULT', $content );
			}
		}

		if( $content )
			return $content;
		else
			return false;
	}

	public function onDisplay( $user )
	{
		if( !$this->allowedPrivacy( $user ) )
		{
			return;
		}

		$this->set( 'email', $this->escape( $user->email ) );

		return $this->display();
	}

	public function onOAuthGetUserPermission( &$permissions )
	{
		$permissions[] = 'email';
	}

	public function onOAuthGetMetaFields( &$fields )
	{
		$fields[] = 'email';
	}
}

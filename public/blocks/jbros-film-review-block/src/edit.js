/**
 * Retrieves the translation of text.
 *
 * @see https://developer.wordpress.org/block-editor/reference-guides/packages/packages-i18n/
 */
import { __ } from '@wordpress/i18n';

import apiFetch from '@wordpress/api-fetch';

import { useEffect } from 'react';

import { debounce } from 'lodash';

/**
 * React hook that is used to mark the block wrapper element.
 * It provides all the necessary props like the class name.
 *
 * @see https://developer.wordpress.org/block-editor/reference-guides/packages/packages-block-editor/#useblockprops
 */
import { useBlockProps, InspectorControls } from '@wordpress/block-editor';

import { Disabled, PanelBody, PanelRow, ToggleControl, QueryControls, TextControl, SelectControl, TextareaControl, Button   } from '@wordpress/components';

/**
 * Lets webpack process CSS, SASS or SCSS files referenced in JavaScript files.
 * Those files can contain any CSS code that gets applied to the editor.
 *
 * @see https://www.npmjs.com/package/@wordpress/scripts#using-css
 */
import './editor.scss';

/**
 * The edit function describes the structure of your block in the context of the
 * editor. This represents what the editor will render when the block is used.
 *
 * @see https://developer.wordpress.org/block-editor/reference-guides/block-api/block-edit-save/#edit
 *
 * @return {Element} Element to render.
 */
export default function Edit( { attributes, setAttributes } ) {


	const { title, year, film_review, action } = attributes;

	const onChangeTitle = ( newTitle ) => {
		setAttributes( { title: newTitle } );
        //debouncedFetchFilmReview();
	};
	const onChangeYear = ( newYear ) => {
		setAttributes( { year: newYear } );
        //debouncedFetchFilmReview();
	};
	const onChangeAction = (newAction) => {
        setAttributes({ action: newAction });
    };

	const onChangeFilmReview = (newFilmReview) => {
        setAttributes({ film_review: newFilmReview });
    };

	// Fetch the film review from the API

	/*
	
	POST /wp-json/jbros-film-review/v1/get-film-review
	{
		"title": "Jaws",
		"year": 1975,
		"action": "get_existing"
	}

	*/
    const fetchFilmReview = async () => {

		if(!title || !year) {
			console.log('year or title missing');
			return;
		}

		console.log('fetch film called, title: ' + title + ', year: ' + year);

		if(year < 1850) {
			console.log('year too small passed: ' + year);
			return;
		}

		//console.log('would call api'); return;

        const response = await apiFetch( {
            path: '/jbros-film-review/v1/get-film-review',
            method: 'POST',
            data: {
                title: title,
                year: year,
				action: action,
            },
        } );

		console.log(response);

        if ( response.success ) {
            setAttributes( { film_review: response.data.film_review } );
        } else {
            setAttributes( { film_review: response.message } );
        }
    };

	//const debouncedFetchFilmReview = debounce(fetchFilmReview, 1500); // Adjust the debounce delay as needed

	// Fetch the film review when the title or year changes
	//v1
	/*
    useEffect( () => {
        if ( title && year ) {
            fetchFilmReview();
        }
    }, [ title, year, action ] );
	*/

	//v2, debounce, never really worked
	/*
	useEffect(() => {
        return () => {
            debouncedFetchFilmReview.cancel();
        };
    }, []);
	*/

	return (
		<>
			<InspectorControls>
				<PanelBody
					title={ __( 'Settings', 'jbros-film-review-block' ) }
					initialOpen={ true }
				>
					<PanelRow>
						<TextControl
							label={ __( 'Film title', 'jbros-film-review-block' ) }
							value={ title }
							onChange={ onChangeTitle }
							help={ __(
								'Name of the film to get review for',
								'jbros-film-review-block'
							) }
						/>
					</PanelRow>
					<PanelRow>
						<TextControl
							label={ __( 'Film year', 'jbros-film-review-block' ) }
							value={ year }
							onChange={ onChangeYear }
							help={ __(
								'Year of the film to get review for',
								'jbros-film-review-block'
							) }
						/>
					</PanelRow>
					<PanelRow>
                        <SelectControl
                            label={__('Action', 'jbros-film-review-block')}
                            value={action}
                            onChange={onChangeAction}
                            options={[
                                { value: 'create_or_update', label: __('Create or Update', 'jbros-film-review-block') },
                                { value: 'get_existing', label: __('Get Existing', 'jbros-film-review-block') },
                            ]}
                        />
                    </PanelRow>
					<PanelRow>
						<TextareaControl
							label="Film Review editor"
							help="You can modify the Film review text here"
							value={ film_review }
							onChange={ onChangeFilmReview }
						/>
                    </PanelRow>
					<PanelRow>
						<Button isPrimary onClick={fetchFilmReview}>
							{__('Fetch Film Review', 'jbros-film-review-block')}
						</Button>
					</PanelRow>
				</PanelBody>
			</InspectorControls>
			<div>{title}</div>
			<div>{year}</div>
		
			<div { ...useBlockProps() }>
				{ film_review ? film_review : 'Add review film and year' }
			</div>
		</>
	);
}

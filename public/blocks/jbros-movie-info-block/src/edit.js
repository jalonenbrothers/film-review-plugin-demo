/**
 * Retrieves the translation of text.
 *
 * @see https://developer.wordpress.org/block-editor/reference-guides/packages/packages-i18n/
 */
import { __ } from '@wordpress/i18n';

/**
 * React hook that is used to mark the block wrapper element.
 * It provides all the necessary props like the class name.
 *
 * @see https://developer.wordpress.org/block-editor/reference-guides/packages/packages-block-editor/#useblockprops
 */
import { useBlockProps, InspectorControls } from '@wordpress/block-editor';

import { PanelBody, PanelRow, SelectControl, TextControl } from '@wordpress/components';
import apiFetch from '@wordpress/api-fetch';
import { useEffect } from '@wordpress/element';

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

export default function Edit({ attributes, setAttributes }) {
    const { title, year, action, movieId, posterUrl } = attributes;

    const onChangeTitle = (newTitle) => {
        setAttributes({ title: newTitle });
    };

    const onChangeYear = (newYear) => {
        setAttributes({ year: newYear });
    };

    const onChangeAction = (newAction) => {
        setAttributes({ action: newAction });
    };

    const fetchMovieData = async () => {
        const response = await apiFetch({
            path: '/jbros-film-review/v1/get-movie-info',
            method: 'POST',
            data: {
                title: title,
                year: year,
                action: action,
            },
        });

        if (response.success) {
            setAttributes({
                movieId: response.data.movie_id,
                posterUrl: response.data.poster_url,
            });
        } else {
            setAttributes({
                movieId: '',
                posterUrl: '',
            });
        }
    };

    useEffect(() => {
        if (title && year) {
            fetchMovieData();
        }
    }, [title, year, action]);

    return (
        <>
            <InspectorControls>
                <PanelBody title="Movie Info" initialOpen={true}>
                    <PanelRow>
                        <TextControl
                            label="Movie Title"
                            value={title}
                            onChange={onChangeTitle}
                        />
                    </PanelRow>
                    <PanelRow>
                        <TextControl
                            label="Movie Year"
                            value={year}
                            onChange={onChangeYear}
                        />
                    </PanelRow>
                    <PanelRow>
                        <SelectControl
                            label="Action"
                            value={action}
                            onChange={onChangeAction}
                            options={[
                                { value: 'create_or_update', label: 'Create or Update' },
                                { value: 'get_existing', label: 'Get Existing' },
                            ]}
                        />
                    </PanelRow>
                </PanelBody>
            </InspectorControls>
            <div {...useBlockProps()}>
                <h3>Movie Info</h3>
                {movieId && <p>Movie ID: {movieId}</p>}
                {posterUrl && <img src={posterUrl} alt="Movie Poster" />}
            </div>
        </>
    );
}
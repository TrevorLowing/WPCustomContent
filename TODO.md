# Fedx.io TODO List

## Content Management
- [ ] Create custom post type for Articles
- [ ] Set up EmbedPress integration for Articles
- [ ] Design blog layout and templates
- [ ] Configure custom taxonomies if needed

## YouTube Integration
- [ ] Implement YouTube playlist page
  - [ ] Follow EmbedPress documentation: [YouTube Channel Embedding Guide](https://embedpress.com/docs/how-to-embed-youtube-channel-in-wordpress/)
  - [ ] Configure playlist layout and styling
  - [ ] Add playlist navigation
  - [ ] Implement caching for better performance

## Future Enhancements
- [ ] Add category filters for Articles
- [ ] Implement search functionality
- [ ] Add social sharing buttons
- [ ] Create responsive design for all new features

## Documentation
- [ ] Document custom post type usage
- [ ] Create user guide for content creators
- [ ] Add code comments for developers

## Metaboxcustome Post Type
add_filter('rwmb_meta_boxes', 'content_meta_boxes');
function content_meta_boxes($meta_boxes) {
    $meta_boxes[] = [
        'title' => 'Content Details',
        'post_types' => 'content',
        'fields' => [
            [
                'id' => 'content_type',
                'name' => 'Content Type',
                'type' => 'select',
                'options' => [
                    'article' => 'Article/Text',
                    'document' => [
                        'pdf' => 'PDF Document',
                        'doc' => 'Word Document',
                        'ppt' => 'PowerPoint',
                        'xls' => 'Excel Spreadsheet',
                        'google_doc' => 'Google Doc',
                        'google_sheet' => 'Google Sheet',
                        'google_slide' => 'Google Slides',
                        'google_form' => 'Google Form'
                    ],
                    'video' => [
                        'youtube' => 'YouTube Video',
                        'vimeo' => 'Vimeo',
                        'wistia' => 'Wistia',
                        'twitch' => 'Twitch Stream'
                    ],
                    'social' => [
                        'facebook' => 'Facebook Post',
                        'instagram' => 'Instagram Post',
                        'twitter' => 'Twitter/X Post',
                        'tiktok' => 'TikTok'
                    ],
                    'audio' => [
                        'spotify' => 'Spotify',
                        'soundcloud' => 'SoundCloud',
                        'mixcloud' => 'MixCloud'
                    ],
                    'image' => [
                        'image_file' => 'Image File',
                        'giphy' => 'Giphy',
                        'imgur' => 'Imgur',
                        'flickr' => 'Flickr'
                    ],
                    'map' => 'Google Maps',
                    'other' => 'Other File Type'
                ],
                'placeholder' => 'Select Content Type'
            ],
            [
                'id' => 'original_url',
                'name' => 'Original URL',
                'type' => 'url',
            ],
            [
                'id' => 'local_file',
                'name' => 'Upload Local Copy',
                'type' => 'file_advanced',
                'max_file_uploads' => 1,
            ],
            [
                'id' => 'embedpress_shortcode',
                'name' => 'EmbedPress Shortcode',
                'type' => 'text',
                'desc' => 'Paste the EmbedPress shortcode here',
            ],
            [
                'id' => 'summary',
                'name' => 'Summary',
                'type' => 'textarea',
            ],
            [
                'id' => 'tagline',
                'name' => 'Tagline',
                'type' => 'text',
            ],
            [
                'id' => 'source_attribution',
                'name' => 'Source Attribution',
                'type' => 'text',
            ],
            [
                'id' => 'original_publish_date',
                'name' => 'Original Publish Date',
                'type' => 'date',
            ],
            [
                'id' => 'repurposed_content',
                'name' => 'Repurposed Content',
                'type' => 'wysiwyg',
            ]
        ]
    ];
    return $meta_boxes;
}


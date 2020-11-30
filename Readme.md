# Higher Forces `wp-cli` toolkit.

These wp-cli packages are designed to service the data and content related to `higherforces.com`.

* `hf-common`
    * Provides helpers for initializing plugins and working with plugin specific and hf specific data.

* `hf-dewpcf` 
	* Converts `wordpress-custom-fields` metadata to human readable form.
	* To be run once during theme transition.

* `hf-cleanup` 
	* Normalize metadata related to sessions.

* `hf-sessionbackup` 
	* Export all session transcripts to files.

* `hf-soundcloudsync`
	* Load a list of soundcloud metadata and match the audio url to sessions based on titles or meta keys.

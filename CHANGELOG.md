Changelog
============

v1.2.0
- [db.php]
- Possibility to not sanitize string for special operator;
- log_error changed from mysqli_error() to $query because first doesn't work good
- Fixed params in result_array() -> mysqli_fetch_assoc() and result_array_values() -> mysqli_fetch_assoc()

- [admin/index.php]
- function old_videos()
- Setted "Unlisted" option by default on upload videos
- Setted 1 for default category
- Added check for old videos and their deletion

- [css/style.css]
- Added style #old_videos and .old_warn

v1.1.8
- Fixed .wmv file extension
- Fixed upload of null
- Fixed with another config.php file
- Cleaned PaxHeader folder creaded by Google Drive
- Added error credential message
- Moved to global variables username and password
- Deletion of unsupported files
- Added strtolower() to getExtension()

v1.1
- First release
- Chucks upload with Resumable.js
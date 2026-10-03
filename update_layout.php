<?php
$files = glob('resources/views/livewire/manage/**/*.blade.php');
$files = array_merge($files, glob('resources/views/livewire/manage/*.blade.php'));
foreach ($files as $file) {
    $content = file_get_contents($file);
    
    // Fix the syntax error from previous regex
    $content = str_replace(
        "#[Layout('layouts.manage')]\nnew class extends Component {",
        "new #[Layout('layouts.manage')] class extends Component {",
        $content
    );
    
    file_put_contents($file, $content);
    echo "Updated $file\n";
}

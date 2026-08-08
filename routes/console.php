<?php

use Illuminate\Support\Facades\Artisan;
use App\Models\Repository;
use App\Models\User;
use App\Http\Controllers\RepositoryController;

Artisan::command('git:validate-push {repositoryName} {userEmail?}', function (string $repositoryName, ?string $userEmail = null) {
    // 1. Read updates from stdin
    $stdin = file_get_contents('php://stdin');

    // 2. Parse updates
    $updates = [];
    if (preg_match_all('/([0-9a-f]{40})\s+([0-9a-f]{40})\s+(refs\/[^\s\0\n]+)/', $stdin, $refMatches, PREG_SET_ORDER)) {
        foreach ($refMatches as $m) {
            $updates[] = [
                'old' => $m[1],
                'new' => $m[2],
                'ref' => $m[3],
            ];
        }
    }

    if (empty($updates)) {
        return 0; // nothing to validate
    }

    // 3. Resolve repository and user
    $repository = Repository::where('name', $repositoryName)->first();
    if (! $repository) {
        fwrite(STDERR, "[SIMCR] Repository '{$repositoryName}' not found in database.\n");
        return 1;
    }

    $pushingUser = $userEmail ? User::where('email', $userEmail)->first() : null;

    $basePath = base_path(config('services.repository.base_path', '../repositories'));
    $repoPath = $basePath.'/'.$repositoryName.'.git';

    // 4. Run validation using RepositoryController
    $controller = app(RepositoryController::class);
    $violation = $controller->validatePushCommitMessages($updates, $repoPath, $repository, $pushingUser);

    if ($violation) {
        fwrite(STDERR, $violation . "\n");
        return 1;
    }

    return 0;
})->purpose('Validate git push commit messages against SIMCR standards');

@component('mail::message')
# Sync status

Hi your sync report for connector: {{ $connector->name }},
<br>
| Status        | Count                   |
| ------------- |:-----------------------:|
| Synced        | {{ $syncedCount }}      |
| Failed        | {{ $failedCount }}      |
| Pending       | {{ $pendingCount }}     |

<br>
Thanks,<br>
{{ config('app.name') }}
@endcomponent

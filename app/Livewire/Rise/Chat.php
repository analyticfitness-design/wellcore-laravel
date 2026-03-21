<?php

namespace App\Livewire\Rise;

use App\Models\AccountabilityPod;
use App\Models\Client;
use App\Models\PodMember;
use App\Models\PodMessage;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Validate;
use Livewire\Component;

#[Layout('layouts.rise', ['title' => 'Chat RISE'])]
class Chat extends Component
{
    #[Validate('required|string|min:1|max:1000')]
    public string $newMessage = '';

    public ?int $podId = null;
    public ?string $podName = null;
    public int $memberCount = 0;

    public function mount(): void
    {
        $client = auth('wellcore')->user();

        // Find the client's pod
        $membership = PodMember::where('client_id', $client->id)->first();

        if ($membership) {
            $pod = AccountabilityPod::find($membership->pod_id);
            if ($pod) {
                $this->podId = $pod->id;
                $this->podName = $pod->name;
                $this->memberCount = PodMember::where('pod_id', $pod->id)->count();
            }
        }
    }

    public function sendMessage(): void
    {
        $this->validate();

        $client = auth('wellcore')->user();

        if (! $this->podId) {
            return;
        }

        PodMessage::create([
            'pod_id' => $this->podId,
            'client_id' => $client->id,
            'message' => trim($this->newMessage),
        ]);

        $this->newMessage = '';
        $this->dispatch('message-sent');
    }

    public function render()
    {
        $messages = [];

        if ($this->podId) {
            $rawMessages = PodMessage::where('pod_id', $this->podId)
                ->orderBy('created_at', 'asc')
                ->limit(100)
                ->get();

            $clientIds = $rawMessages->pluck('client_id')->unique()->filter();
            $clients = Client::whereIn('id', $clientIds)->get()->keyBy('id');

            $currentClientId = auth('wellcore')->id();

            foreach ($rawMessages as $msg) {
                $msgClient = $clients->get($msg->client_id);
                $messages[] = [
                    'id' => $msg->id,
                    'message' => $msg->message,
                    'name' => $msgClient->name ?? 'Usuario',
                    'initial' => substr($msgClient->name ?? 'U', 0, 1),
                    'isOwn' => (string) $msg->client_id === (string) $currentClientId,
                    'time' => $msg->created_at?->format('H:i') ?? '',
                    'date' => $msg->created_at?->translatedFormat('d M') ?? '',
                ];
            }
        }

        return view('livewire.rise.chat', [
            'messages' => $messages,
        ]);
    }
}

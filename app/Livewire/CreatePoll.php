<?php

namespace App\Livewire;

use App\Actions\Polls\CreatePoll as CreatePollAction;
use App\Http\PollAdminSession;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\Validator;
use Livewire\Attributes\Locked;
use Livewire\Component;

class CreatePoll extends Component
{
    public string $title = '';

    #[Locked]
    public array $dates = [];

    #[Locked]
    public int $step = 1;

    #[Locked]
    public string $month;

    #[Locked]
    public ?string $createdPublicId = null;

    public function mount(): void
    {
        $this->month = CarbonImmutable::now('Europe/Copenhagen')->startOfMonth()->toDateString();
    }

    public function next(): void
    {
        $this->title = trim($this->title);
        $this->validate(['title' => ['required', 'string', 'max:140']], [
            'title.required' => 'Skriv, hvad I skal finde en dag til.',
            'title.max' => 'Brug højst 140 tegn til spørgsmålet.',
        ]);
        $this->step = 2;
    }

    public function back(): void
    {
        $this->step = max(1, $this->step - 1);
    }

    public function review(): void
    {
        $this->validate(['title' => ['required', 'string', 'max:140'], 'dates' => ['required', 'array', 'min:2', 'max:60']], [
            'dates.min' => 'Vælg mindst to datoer.',
        ]);
        $this->step = 3;
    }

    public function changeMonth(int $direction): void
    {
        abort_unless(in_array($direction, [-1, 1], true), 422);
        $this->month = CarbonImmutable::parse($this->month)->addMonths($direction)->toDateString();
    }

    public function toggleDate(string $date): void
    {
        Validator::make(['date' => $date], ['date' => ['required', 'date_format:Y-m-d']])->validate();
        $this->resetValidation('dates');
        if (in_array($date, $this->dates, true)) {
            $this->dates = array_values(array_diff($this->dates, [$date]));
        } elseif (count($this->dates) < 60) {
            $this->dates[] = $date;
            sort($this->dates);
        } else {
            $this->addError('dates', 'Vælg højst 60 datoer.');
        }
    }

    public function create(CreatePollAction $create, PollAdminSession $session): void
    {
        if ($this->createdPublicId) {
            $this->redirectRoute('polls.share', $this->createdPublicId);

            return;
        }
        $created = $create->handle($this->title, $this->dates);
        $session->grant($created->adminAccess, $created->adminToken);
        $this->createdPublicId = $created->poll->public_id;
        $this->redirectRoute('polls.share', $created->poll);
    }

    public function render()
    {
        $month = CarbonImmutable::parse($this->month)->locale('da');

        return view('livewire.create-poll', [
            'calendarMonth' => $month,
            'days' => collect(range(1, $month->daysInMonth))->map(fn ($day) => $month->day($day)),
        ]);
    }
}

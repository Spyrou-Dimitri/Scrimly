<?php

namespace App\Enums;

enum StatusTask: string
{
    case TODO = 'todo';
    case IN_PROGRESS = 'in_progress';
    case DONE = 'done';

    public function label(): string
    {
        return match ($this) {
            self::TODO => 'À faire',
            self::IN_PROGRESS => 'En cours',
            self::DONE => 'Terminé',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::TODO => '#71768B',
            self::IN_PROGRESS => '#3B82F6',
            self::DONE => '#02E676',
        };
    }

    public function macaron(): string
    {
        return match ($this) {
            self::TODO => 'text-white rounded-full font-bold bg-task-todo py-2 px-4',
            self::IN_PROGRESS => 'text-white rounded-full font-bold bg-task-in-progress py-2 px-4',
            self::DONE => 'text-[#046143] rounded-full font-bold bg-task-done py-2 px-4',
        };
    }

    public function borderColor(): string
    {
        return match ($this) {
            self::TODO => 'border-task-todo',
            self::IN_PROGRESS => 'border-task-in-progress',
            self::DONE => 'border-task-done',
        };
    }

    public function textColor(): string
    {
        return match ($this) {
            self::TODO => 'text-white',
            self::IN_PROGRESS => 'text-task-in-progress',
            self::DONE => 'text-task-done',
        };
    }

    public function backgroundColor(): string
    {
        return match ($this) {
            self::TODO => 'bg-task-todo',
            self::IN_PROGRESS => 'bg-task-in-progress',
            self::DONE => 'bg-task-done',
        };
    }
}

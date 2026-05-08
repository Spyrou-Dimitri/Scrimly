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
            self::TODO => 'text-task-todo',
            self::IN_PROGRESS => 'text-task-in-progress',
            self::DONE => 'text-task-done',
        };
    }
}   
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

    public function macaron(): string
    {
        return match ($this) {
            self::TODO => 'text-white bg-task-todo font-bold p-2 rounded-md',
            self::IN_PROGRESS => 'text-white bg-task-in-progress font-bold p-2 rounded-md',
            self::DONE => 'text-white bg-task-done font-bold p-2 rounded-md',
        };
    }

}   
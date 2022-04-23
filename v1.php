<?php

// $baseX: The corner of the map representing your base
fscanf(STDIN, "%d %d", $baseX, $baseY);
$base = new Base($baseX, $baseY);
// $heroesPerPlayer: Always 3
fscanf(STDIN, "%d", $heroesPerPlayer);

// game loop
while (TRUE)
{
    for ($i = 0; $i < 2; $i++)
    {
        // $health: Each player's base health
        // $mana: Ignore in the first league; Spend ten mana to cast a spell
        fscanf(STDIN, "%d %d", $health, $mana);
    }
    // $entityCount: Amount of heros and monsters you can see
    fscanf(STDIN, "%d", $entityCount);
    $monsters = [];
    $heroes = [];
    $enemies = [];
    for ($i = 0; $i < $entityCount; $i++)
    {
        // $id: Unique identifier
        // $type: 0=monster, 1=your hero, 2=opponent hero
        // $x: Position of this entity
        // $shieldLife: Ignore for this league; Count down until shield spell fades
        // $isControlled: Ignore for this league; Equals 1 when this entity is under a control spell
        // $health: Remaining health of this monster
        // $vx: Trajectory of this monster
        // $nearBase: 0=monster with no target yet, 1=monster targeting a base
        // $threatFor: Given this monster's trajectory, is it a threat to 1=your base, 2=your opponent's base, 0=neither
        fscanf(STDIN, "%d %d %d %d %d %d %d %d %d %d %d", $id, $type, $x, $y, $shieldLife, $isControlled, $health, $vx, $vy, $nearBase, $threatFor);
        if ($type == 0) {
            $monsters[] = new Monster($x, $y);
        } elseif ($type == 1) {
            $heroes[] = new Hero($x, $y);
        } elseif ($type == 2) {
            $enemies[] = new Entity($x, $y);
        }
    }
    
    $game = new Game($base, $monsters, $heroes, $enemies);

    $game->play();
}

class Entity
{
    public $x;
    public $y;
    public function __construct(int $x, int $y)
    {
        $this->x = $x;
        $this->y = $y;
    }
}

class Base extends Entity
{
}

class Monster extends Entity
{
}

class Hero extends Entity
{
}

class Game
{
    public const X = 17630;
    public const Y = 9000;

    public $base;
    public $monsters;
    public $heroes;
    
    public function __construct(Base $base, array $monsters, array $heroes, array $enemies)
    {
        $this->base = $base;
        $this->monsters = $monsters;
        $this->heroes = $heroes;
        $this->enemies = $enemies;
    }

    public function play()
    {
        // $this->sortMonsters();
        // error_log(var_export($this->monsters, 1));

        $hero1 = $this->heroes[0];
        if ($this->canPush($hero1)) {
            printf("SPELL WIND %d %d %s\n", self::X - $this->base->x, self::Y - $this->base->y, 'Фуух!');
        } else {
            if ($this->base->x == 0) {
                $x = 1100;
                $y = 1100;
            } else {
                $x = self::X - 1100;
                $y = self::Y - 1100;
            }
            printf("MOVE %d %d %s\n", $x, $y, 'Скукотища...');
        }

        $this->monsters = array_filter($this->monsters, function ($monster) {
            return distance($monster, $this->base) > 5000;
        });

        $hero2 = $this->heroes[1];
        if (!$this->monsters) {
            printf("MOVE %d %d %s\n", self::X / 2, self::Y / 4, 'Вперёд!');
        } else {
            $this->sortMonsters($hero2);
            $monster = $this->monsters[0];
            printf("MOVE %d %d %s\n", $monster->x, $monster->y, 'Урааа!');
        }

        $hero3 = $this->heroes[2];
        if (!$this->monsters) {
            printf("MOVE %d %d %s\n", self::X / 2, self::Y * 3 / 4, 'Вперёд!');
        } else {
            $this->sortMonsters($hero3);
            $prevMonster = $monster;
            $monster = array_shift($this->monsters);
            if ($monster === $prevMonster && $this->monsters) {
                $monster = array_shift($this->monsters);
            }
            printf("MOVE %d %d %s\n", $monster->x, $monster->y, 'Урааа!');
        }
    }

    private function sortMonsters(Entity $point)
    {
        usort($this->monsters, function ($m1, $m2) use ($point) {
            return distance($m1, $point) <=> distance($m2, $point);
        });
    }

    private function canPush($hero): bool
    {
        foreach ($this->monsters as $monster) {
            if (distance($hero, $monster) <= 1280 && distance($this->base, $monster) < 1000) {
                return true;
            }
        }
        foreach ($this->enemies as $enemy) {
            if (distance($hero, $enemy) <= 1280) {
                return true;
            }
        }
        return false;
    }
}

function distance(Entity $a, Entity $b)
{
    return sqrt(($a->y - $b->y)**2 + ($a->x - $b->x)**2);
}

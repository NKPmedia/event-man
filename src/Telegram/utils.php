<?php


function chatMembersToIds($chatMembers)
{
    return array_map(function ($member) {
        return $member->getUser()->getId();
    }, $chatMembers);
}
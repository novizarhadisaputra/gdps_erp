<?php

namespace App\Ai\Agents;

use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Ai\Attributes\Timeout;
use Laravel\Ai\Contracts\Agent;
use Laravel\Ai\Contracts\HasStructuredOutput;
use Laravel\Ai\Promptable;

#[Timeout(300)]
class ProposalMetadataAgent implements Agent, HasStructuredOutput
{
    use Promptable;

    public function instructions(): string
    {
        return 'Tolong ekstrak informasi penting dari dokumen proposal ini. Saya butuh: 
        1. proposal_number
        2. total_amount (nominal saja)
        3. customer_name
        4. submission_date (format YYYY-MM-DD)';
    }

    public function schema(JsonSchema $schema): array
    {
        return [
            'proposal_number' => $schema->string(),
            'total_amount' => $schema->number(),
            'customer_name' => $schema->string(),
            'submission_date' => $schema->string()->format('date'),
        ];
    }
}

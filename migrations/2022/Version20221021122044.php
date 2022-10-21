<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20221021122044 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE contract RENAME INDEX idx_10f94a0f12e5fe42 TO IDX_E98F285912E5FE42');
        $this->addSql('ALTER TABLE contract RENAME INDEX idx_10f94a0f40c484b8 TO IDX_E98F285940C484B8');
        $this->addSql('ALTER TABLE contract RENAME INDEX idx_10f94a0f85357f53 TO IDX_E98F285985357F53');
        $this->addSql('ALTER TABLE nieuws RENAME INDEX idx_e702bdbe85357f53 TO IDX_E0AEB88885357F53');
        $this->addSql('ALTER TABLE periode RENAME INDEX idx_5c7e146f85357f53 TO IDX_93C32DF385357F53');
        $this->addSql('ALTER TABLE ploeg RENAME INDEX idx_7169769a76ed395 TO IDX_C6D7B86DA76ED395');
        $this->addSql('ALTER TABLE ploeg RENAME INDEX idx_716976985357f53 TO IDX_C6D7B86D85357F53');
        $this->addSql('ALTER TABLE renner RENAME INDEX uniq_9e67198ad8c9db56 TO UNIQ_99CB1CBCD8C9DB56');
        $this->addSql('ALTER TABLE renner RENAME INDEX uniq_9e67198a989d9b62 TO UNIQ_99CB1CBC989D9B62');
        $this->addSql('ALTER TABLE renner RENAME INDEX idx_9e67198af92f3e70 TO IDX_99CB1CBCF92F3E70');
        $this->addSql('ALTER TABLE seizoen RENAME INDEX uniq_b6c3b8d9989d9b62 TO UNIQ_797E8145989D9B62');
        $this->addSql('ALTER TABLE spelregels RENAME INDEX idx_6a86880d85357f53 TO IDX_25DB8BDD85357F53');
        $this->addSql('ALTER TABLE transfer RENAME INDEX idx_b942c19640c484b8 TO IDX_4034A3C040C484B8');
        $this->addSql('ALTER TABLE transfer RENAME INDEX idx_b942c196306eb913 TO IDX_4034A3C0306EB913');
        $this->addSql('ALTER TABLE transfer RENAME INDEX idx_b942c196d22b05f TO IDX_4034A3C0D22B05F');
        $this->addSql('ALTER TABLE transfer RENAME INDEX idx_b942c19685357f53 TO IDX_4034A3C085357F53');
        $this->addSql('ALTER TABLE transfer RENAME INDEX uniq_b942c1965c2409ae TO UNIQ_4034A3C05C2409AE');
        $this->addSql('ALTER TABLE uitslag RENAME INDEX idx_585f92d89976dc14 TO IDX_97E2AB449976DC14');
        $this->addSql('ALTER TABLE uitslag RENAME INDEX idx_585f92d840c484b8 TO IDX_97E2AB4440C484B8');
        $this->addSql('ALTER TABLE uitslag RENAME INDEX idx_585f92d812e5fe42 TO IDX_97E2AB4412E5FE42');
        $this->addSql('ALTER TABLE user RENAME INDEX uniq_2da1797792fc23a8 TO UNIQ_8D93D64992FC23A8');
        $this->addSql('ALTER TABLE user RENAME INDEX uniq_2da17977a0d96fbf TO UNIQ_8D93D649A0D96FBF');
        $this->addSql('ALTER TABLE user RENAME INDEX uniq_2da17977c05fb297 TO UNIQ_8D93D649C05FB297');
        $this->addSql('ALTER TABLE wedstrijd RENAME INDEX idx_c5d1798c85357f53 TO IDX_4720FB2F85357F53');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE contract RENAME INDEX idx_e98f285912e5fe42 TO IDX_10F94A0F12E5FE42');
        $this->addSql('ALTER TABLE contract RENAME INDEX idx_e98f285985357f53 TO IDX_10F94A0F85357F53');
        $this->addSql('ALTER TABLE contract RENAME INDEX idx_e98f285940c484b8 TO IDX_10F94A0F40C484B8');
        $this->addSql('ALTER TABLE nieuws RENAME INDEX idx_e0aeb88885357f53 TO IDX_E702BDBE85357F53');
        $this->addSql('ALTER TABLE periode RENAME INDEX idx_93c32df385357f53 TO IDX_5C7E146F85357F53');
        $this->addSql('ALTER TABLE ploeg RENAME INDEX idx_c6d7b86da76ed395 TO IDX_7169769A76ED395');
        $this->addSql('ALTER TABLE ploeg RENAME INDEX idx_c6d7b86d85357f53 TO IDX_716976985357F53');
        $this->addSql('ALTER TABLE renner RENAME INDEX idx_99cb1cbcf92f3e70 TO IDX_9E67198AF92F3E70');
        $this->addSql('ALTER TABLE renner RENAME INDEX uniq_99cb1cbc989d9b62 TO UNIQ_9E67198A989D9B62');
        $this->addSql('ALTER TABLE renner RENAME INDEX uniq_99cb1cbcd8c9db56 TO UNIQ_9E67198AD8C9DB56');
        $this->addSql('ALTER TABLE seizoen RENAME INDEX uniq_797e8145989d9b62 TO UNIQ_B6C3B8D9989D9B62');
        $this->addSql('ALTER TABLE spelregels RENAME INDEX idx_25db8bdd85357f53 TO IDX_6A86880D85357F53');
        $this->addSql('ALTER TABLE transfer RENAME INDEX idx_4034a3c0d22b05f TO IDX_B942C196D22B05F');
        $this->addSql('ALTER TABLE transfer RENAME INDEX uniq_4034a3c05c2409ae TO UNIQ_B942C1965C2409AE');
        $this->addSql('ALTER TABLE transfer RENAME INDEX idx_4034a3c040c484b8 TO IDX_B942C19640C484B8');
        $this->addSql('ALTER TABLE transfer RENAME INDEX idx_4034a3c085357f53 TO IDX_B942C19685357F53');
        $this->addSql('ALTER TABLE transfer RENAME INDEX idx_4034a3c0306eb913 TO IDX_B942C196306EB913');
        $this->addSql('ALTER TABLE uitslag RENAME INDEX idx_97e2ab4440c484b8 TO IDX_585F92D840C484B8');
        $this->addSql('ALTER TABLE uitslag RENAME INDEX idx_97e2ab4412e5fe42 TO IDX_585F92D812E5FE42');
        $this->addSql('ALTER TABLE uitslag RENAME INDEX idx_97e2ab449976dc14 TO IDX_585F92D89976DC14');
        $this->addSql('ALTER TABLE user RENAME INDEX uniq_8d93d64992fc23a8 TO UNIQ_2DA1797792FC23A8');
        $this->addSql('ALTER TABLE user RENAME INDEX uniq_8d93d649a0d96fbf TO UNIQ_2DA17977A0D96FBF');
        $this->addSql('ALTER TABLE user RENAME INDEX uniq_8d93d649c05fb297 TO UNIQ_2DA17977C05FB297');
        $this->addSql('ALTER TABLE wedstrijd RENAME INDEX idx_4720fb2f85357f53 TO IDX_C5D1798C85357F53');
    }
}

<?php
declare(strict_types=1);

namespace Vanssata\SyliusPagOnlineImpresePlugin\Form\Type;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\UrlType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints\Choice;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Range;
use Vanssata\SyliusPagOnlineImpresePlugin\Payum\PagOnlineImpreseApi;

final class PagOnlineImpreseGatewayConfigurationType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder->add('tId', TextType::class,[
            'label' => "Terminal ID (get from bank)",
            'help' => 'contact to your bank',
            'required' => true,
            'constraints' => [
                new NotBlank(["message"=>"The terminal cannot be empty !!!"])
            ]
        ])
            ->add('kSing', TextType::class,[
            'label' => "K Sing  ",
            "help" => "contact to your bank",
            'required' => true,
            'constraints' => [
                new NotBlank(['message'=>"The terminal cannot be empty !!!"])
            ]
        ])  ->add('serverUrl', UrlType::class,[
            'label' => "Server URL",
            "help" => "URL when customer has been redirected",
            "empty_data" => "https://netswgroup.it/UNI_CG_SERVICES/services",
            'required' => true,
            'constraints' => [
                new NotBlank(['message'=>"The terminal cannot be empty !!!"])
            ]
        ])
            ->add('trType',ChoiceType::class,[
                'label' => "trType",
                'empty_data' => PagOnlineImpreseApi::AVALIABLE_TRTYPES[0],
                'help' => sprintf('Set type of transactions, by default use %s',PagOnlineImpreseApi::AVALIABLE_TRTYPES[0]),
                'choices' => array_combine(
                    PagOnlineImpreseApi::AVALIABLE_TRTYPES,
                    PagOnlineImpreseApi::AVALIABLE_TRTYPES
                ),
                'constraints' => [
                    new Choice([
                        'choices' => PagOnlineImpreseApi::AVALIABLE_TRTYPES
                    ])
                ]
            ])
            ->add('timeOut',IntegerType::class, [
            'label' => 'Set timeout before cancel connection',
            'help' => 'range form 360 to 360000',
            'empty_data' => 3600,
                'constraints' => [
                    new Range([
                        'min' => 360,
                        'max' => 360000
                    ])
                ]
        ])
        ->add('testMode', ChoiceType::class,[
            'label' => 'Test Mode',
            'help' => 'If you check test mode, you just test process, but not get money',
            'choices' => [
                'YES' => 1,
                'NO' => 0
            ]
        ]);
    }
}

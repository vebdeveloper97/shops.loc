<aside class="main-sidebar">

    <section class="sidebar">

        <!-- Sidebar user panel -->
        <div class="user-panel">
            <div class="pull-left image">
                <img src="<?= $directoryAsset ?>/img/user2-160x160.jpg" class="img-circle" alt="User Image"/>
            </div>
            <div class="pull-left info">
                <p>Alexander Pierce</p>

                <a href="#"><i class="fa fa-circle text-success"></i> Online</a>
            </div>
        </div>

        <!-- search form -->
        <form action="#" method="get" class="sidebar-form">
            <div class="input-group">
                <input type="text" name="q" class="form-control" placeholder="Search..."/>
              <span class="input-group-btn">
                <button type='submit' name='search' id='search-btn' class="btn btn-flat"><i class="fa fa-search"></i>
                </button>
              </span>
            </div>
        </form>
        <!-- /.search form -->
        <?php
            $controller = Yii::$app->controller->id;
            $action = Yii::$app->controller->action->id;
            $slug = Yii::$app->request->get('slug');
        ?>
        <?= dmstr\widgets\Menu::widget(
            [
                'options' => ['class' => 'sidebar-menu tree', 'data-widget'=> 'tree'],
                'items' => [
                    [
                        'label' => 'Do\'kon',
                        'icon' => 'share',
                        'url' => '#',
                        'items' => [
                            [
                                'label' => 'Maxsulot qabul qilish',
                                'icon' => 'cart-plus',
                                'url' => ['product-document/incoming/index'],
                                'active' =>
                                    $controller == 'product-document'
                                    && $slug == 'incoming',
                            ],
                            [
                                'label' => 'Maxsulot sotish',
                                'icon' => 'cart-arrow-down',
                                'url' => ['product-document/selling/index'],
                                'active' =>
                                    $controller == 'product-document'
                                    && $slug == 'selling',
                            ],
                            [
                                'label' => 'Hisobot',
                                'icon' => 'bars',
                                'url' => ['product-document/report/index'],
                                'active' =>
                                    $controller == 'product-document'
                                    && $slug == 'report',
                                ],
                            [
                                'label' => 'Maxsulot',
                                'icon' => 'plus',
                                'url' => ['product/index'],
                                'active' =>
                                    $controller == 'product'
                                ],
                        ],
                    ],
                ],
            ]
        ) ?>

    </section>

</aside>
